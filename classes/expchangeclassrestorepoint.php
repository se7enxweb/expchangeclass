<?php
/**
 * File containing the expChangeClassRestorePoint class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The restore point of a class change: every attribute row of the object (all versions and languages, as stored),
 * its relation rows and its class, written to var/<site>/expchangeclass/restorepoints/<id>.json before the change
 * writes anything. Without it the change does not start.
 *
 * The change keeps what it drops: the data of an attribute the new class does not take over (its files, its
 * image aliases, its rows in the datatype's own tables) stays where it was, only the attribute row goes, so
 * restore() can put the rows back and everything is there again. discard() lets that go for good: it removes the
 * dropped attributes' stored data the way removing an attribute always did (the datatype's
 * deleteStoredObjectAttribute()) and keeps only the record of the change.
 *
 * States: pending (written, the change is running or failed before it committed), applied, restored, discarded,
 * failed. restore() refuses when the object was edited after the change (a version was added or removed), when its
 * class is no longer the one it was changed into, or when the old class lost an attribute since.
 *
 * Files are written atomically and readable by the site user and by Exponential Velocity alike. On a cluster the
 * directory must be shared, as the content jobs' is.
 */
class expChangeClassRestorePoint
{
    const PENDING = 'pending';
    const APPLIED = 'applied';
    const RESTORED = 'restored';
    const DISCARDED = 'discarded';
    const FAILED = 'failed';

    const FORMAT = 'expchangeclass-restorepoint/1';

    /** @var string|null set by tests */
    public static $directory = null;

    /**
     * The directory of the restore points.
     *
     * @return string
     */
    public static function directory()
    {
        if ( self::$directory !== null )
            return self::$directory;
        return eZSys::varDirectory() . '/expchangeclass/restorepoints';
    }

    /**
     * Whether an id is the form create() makes (nothing else is ever read or written).
     *
     * @param string $id
     * @return bool
     */
    public static function validID( $id )
    {
        return is_string( $id ) && preg_match( '/^\d{8}-\d{6}-\d+-[0-9a-f]{6}$/', $id ) === 1;
    }

    /**
     * The file of a restore point.
     *
     * @param string $id
     * @return string|null
     */
    public static function file( $id )
    {
        return self::validID( $id ) ? self::directory() . '/' . $id . '.json' : null;
    }

    /**
     * Reads a restore point.
     *
     * @param string $id
     * @return array|null
     */
    public static function load( $id )
    {
        $file = self::file( $id );
        if ( $file === null || !is_file( $file ) )
            return null;
        $data = json_decode( (string)file_get_contents( $file ), true );
        return is_array( $data ) && isset( $data['format'] ) && $data['format'] === self::FORMAT ? $data : null;
    }

    /**
     * Writes a restore point (atomically).
     *
     * @param array $data
     * @return bool
     */
    public static function save( array $data )
    {
        $file = self::file( $data['id'] );
        if ( $file === null )
            return false;
        $dir = dirname( $file );
        if ( !is_dir( $dir ) && !eZDir::mkdir( $dir, false, true ) )
            return false;
        $json = json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR );
        if ( !is_string( $json ) )
            return false;
        $temporary = $file . '.' . getmypid() . '.tmp';
        if ( @file_put_contents( $temporary, $json, LOCK_EX ) === false )
            return false;
        $permissions = class_exists( 'eZINI' ) ? octdec( eZINI::instance()->variable( 'FileSettings', 'StorageFilePermissions' ) ) : 0666;
        @chmod( $temporary, $permissions );
        if ( !@rename( $temporary, $file ) )
        {
            @unlink( $temporary );
            return false;
        }
        return true;
    }

    /**
     * Takes the restore point of an object before its class is changed.
     *
     * @param eZContentObject $object
     * @param eZContentClass $sourceClass
     * @param eZContentClass $destClass
     * @param array $mapping
     * @param array $meta via (admin, command, job, migration), job_id ...
     * @return array|null the restore point, null when it could not be written
     */
    public static function create( $object, $sourceClass, $destClass, array $mapping, array $meta = array() )
    {
        $db = eZDB::instance();
        $objectID = (int)$object->attribute( 'id' );
        $user = eZUser::currentUser();
        $id = date( 'Ymd-His' ) . '-' . $objectID . '-' . bin2hex( random_bytes( 3 ) );
        $data = array(
            'format' => self::FORMAT,
            'id' => $id,
            'state' => self::PENDING,
            'created' => time(),
            'user_id' => (int)$user->attribute( 'contentobject_id' ),
            'user_login' => (string)$user->attribute( 'login' ),
            'via' => isset( $meta['via'] ) ? (string)$meta['via'] : 'admin',
            'job_id' => isset( $meta['job_id'] ) ? (string)$meta['job_id'] : '',
            'object' => array( 'id' => $objectID, 'name' => (string)$object->attribute( 'name' ),
                               'main_node_id' => (int)$object->attribute( 'main_node_id' ),
                               'section_id' => (int)$object->attribute( 'section_id' ),
                               'modified' => (int)$object->attribute( 'modified' ),
                               'current_version' => (int)$object->attribute( 'current_version' ) ),
            'source_class' => self::classInfo( $sourceClass ),
            'dest_class' => self::classInfo( $destClass ),
            'mapping' => $mapping,
            'versions' => self::versions( $objectID ),
            'attributes' => (array)$db->arrayQuery( "SELECT * FROM ezcontentobject_attribute WHERE contentobject_id = $objectID ORDER BY id, version" ),
            'links' => (array)$db->arrayQuery( "SELECT * FROM ezcontentobject_link WHERE from_contentobject_id = $objectID ORDER BY id" ),
            'after' => array(),
            'history' => array( array( 'state' => self::PENDING, 'time' => time(), 'user_id' => (int)$user->attribute( 'contentobject_id' ) ) ),
            'error' => '',
        );
        return self::save( $data ) ? $data : null;
    }

    /**
     * id, identifier and name of a class, and the ids of its attributes.
     */
    protected static function classInfo( $class )
    {
        $attributes = array();
        foreach ( (array)$class->dataMap() as $identifier => $attribute )
            $attributes[$identifier] = (int)$attribute->attribute( 'id' );
        return array( 'id' => (int)$class->attribute( 'id' ), 'identifier' => (string)$class->attribute( 'identifier' ),
                      'name' => (string)$class->attribute( 'name' ), 'attributes' => $attributes );
    }

    /**
     * The version numbers of an object, sorted.
     *
     * @param int $objectID
     * @return int[]
     */
    public static function versions( $objectID )
    {
        $rows = eZDB::instance()->arrayQuery( 'SELECT version FROM ezcontentobject_version WHERE contentobject_id = ' . (int)$objectID . ' ORDER BY version' );
        $versions = array();
        foreach ( (array)$rows as $row )
            $versions[] = (int)$row['version'];
        return $versions;
    }

    /**
     * Records a state change.
     *
     * @param array $data
     * @param string $state
     * @param array $fields more fields to set (after, error ...)
     * @return array the restore point as saved
     */
    public static function mark( array $data, $state, array $fields = array() )
    {
        $data = array_merge( $data, $fields );
        $data['state'] = $state;
        $data['history'][] = array( 'state' => $state, 'time' => time(), 'user_id' => (int)eZUser::currentUserID() );
        self::save( $data );
        return $data;
    }

    /**
     * Whether a restore point can be restored now, and why not.
     *
     * @param array $data
     * @return string|null null: it can; else the reason (English)
     */
    public static function restoreProblem( array $data )
    {
        if ( $data['state'] !== self::APPLIED )
            return 'This change is ' . $data['state'] . ', there is nothing to restore.';
        $object = eZContentObject::fetch( (int)$data['object']['id'] );
        if ( !$object instanceof eZContentObject )
            return 'The object no longer exists.';
        if ( (int)$object->attribute( 'contentclass_id' ) !== (int)$data['dest_class']['id'] )
            return 'The object is no longer of the class it was changed into.';
        $after = isset( $data['after']['versions'] ) ? (array)$data['after']['versions'] : (array)$data['versions'];
        if ( self::versions( (int)$data['object']['id'] ) !== array_map( 'intval', $after ) )
            return 'The object was edited after the change: restoring would lose that work. Change its class back instead.';
        $sourceClass = eZContentClass::fetch( (int)$data['source_class']['id'] );
        if ( !$sourceClass instanceof eZContentClass )
            return 'The old class no longer exists.';
        $ids = array();
        foreach ( (array)$sourceClass->dataMap() as $attribute )
            $ids[(int)$attribute->attribute( 'id' )] = true;
        foreach ( $data['attributes'] as $row )
        {
            if ( !isset( $ids[(int)$row['contentclassattribute_id']] ) )
                return 'The old class lost an attribute since the change.';
        }
        return null;
    }

    /**
     * Puts an object back as the restore point recorded it, in one transaction.
     *
     * @param array $data
     * @param string|null $error the reason when it was not restored
     * @return array|null the restore point after the restore, null when refused or rolled back
     */
    public static function restore( array $data, &$error = null )
    {
        $error = self::restoreProblem( $data );
        if ( $error !== null )
            return null;
        $objectID = (int)$data['object']['id'];
        $db = eZDB::instance();
        $db->begin();
        try
        {
            $db->query( "DELETE FROM ezcontentobject_attribute WHERE contentobject_id = $objectID" );
            foreach ( $data['attributes'] as $row )
                eZPersistentObject::storeObject( new eZContentObjectAttribute( $row ) );
            $db->query( "DELETE FROM ezcontentobject_link WHERE from_contentobject_id = $objectID" );
            foreach ( $data['links'] as $row )
                self::insertRow( $db, 'ezcontentobject_link', $row );
            $db->query( 'UPDATE ezcontentobject SET contentclass_id = ' . (int)$data['source_class']['id'] . " WHERE id = $objectID" );
            if ( !$db->commit() )
                throw new RuntimeException( 'the transaction could not be committed' );
        }
        catch ( Exception $e )
        {
            $db->rollback();
            $error = 'Restoring was rolled back: ' . $e->getMessage();
            eZDebug::writeError( "Restore point {$data['id']}: " . $e->getMessage(), __METHOD__ );
            return null;
        }
        expChangeClassService::afterChange( $objectID );
        return self::mark( $data, self::RESTORED );
    }

    /**
     * Inserts a row as it was read (every column), escaped.
     */
    protected static function insertRow( $db, $table, array $row )
    {
        $columns = array();
        $values = array();
        foreach ( $row as $column => $value )
        {
            if ( !preg_match( '/^[a-z_][a-z0-9_]*$/', (string)$column ) )
                continue;
            $columns[] = $column;
            $values[] = $value === null ? 'NULL' : ( is_int( $value ) || ( is_string( $value ) && preg_match( '/^-?\d+$/', $value ) ) ? (string)$value : "'" . $db->escapeString( (string)$value ) . "'" );
        }
        $db->query( "INSERT INTO $table (" . implode( ', ', $columns ) . ') VALUES (' . implode( ', ', $values ) . ')' );
    }

    /**
     * Lets the dropped data of an applied change go for good, or forgets a restored or failed one. The record of the
     * change stays (without the rows).
     *
     * @param array $data
     * @return array the restore point after
     */
    public static function discard( array $data )
    {
        if ( $data['state'] === self::APPLIED )
        {
            $db = eZDB::instance();
            $objectID = (int)$data['object']['id'];
            $present = array();
            foreach ( (array)$db->arrayQuery( "SELECT id, version FROM ezcontentobject_attribute WHERE contentobject_id = $objectID" ) as $row )
                $present[(int)$row['id'] . '/' . (int)$row['version']] = true;
            foreach ( $data['attributes'] as $row )
            {
                if ( isset( $present[(int)$row['id'] . '/' . (int)$row['version']] ) )
                    continue;
                // an attribute the change dropped: its stored data goes as it does when an attribute is removed
                try
                {
                    $dataType = eZDataType::create( (string)$row['data_type_string'] );
                    if ( $dataType )
                        $dataType->deleteStoredObjectAttribute( new eZContentObjectAttribute( $row ), (int)$row['version'] );
                }
                catch ( Exception $e )
                {
                    eZDebug::writeWarning( "Restore point {$data['id']}: " . $e->getMessage(), __METHOD__ );
                }
            }
        }
        $data['attributes'] = array();
        $data['links'] = array();
        return self::mark( $data, self::DISCARDED );
    }

    /**
     * The restore points, newest first.
     *
     * @param int $limit
     * @param int|null $userID only this user's
     * @param int|null $objectID only this object's
     * @return array[] the restore points without their rows
     */
    public static function listRecent( $limit = 50, $userID = null, $objectID = null )
    {
        $files = glob( self::directory() . '/*.json' );
        if ( !$files )
            return array();
        rsort( $files );
        $list = array();
        foreach ( $files as $file )
        {
            $id = basename( $file, '.json' );
            if ( !self::validID( $id ) )
                continue;
            if ( $objectID !== null && strpos( $id, '-' . (int)$objectID . '-' ) === false )
                continue;
            $data = self::load( $id );
            if ( !$data || ( $userID !== null && (int)$data['user_id'] !== (int)$userID ) )
                continue;
            if ( $objectID !== null && (int)$data['object']['id'] !== (int)$objectID )
                continue;
            $list[] = self::summary( $data );
            if ( count( $list ) >= $limit )
                break;
        }
        return $list;
    }

    /**
     * A restore point without its rows, with the counts.
     *
     * @param array $data
     * @return array
     */
    public static function summary( array $data )
    {
        $summary = $data;
        $summary['attribute_rows'] = count( $data['attributes'] );
        $summary['link_rows'] = count( $data['links'] );
        unset( $summary['attributes'], $summary['links'] );
        return $summary;
    }

    /**
     * Removes the files of restore points older than a number of days that are not applied any more (restored,
     * discarded, failed) and discards applied ones older than that.
     *
     * @param int $days
     * @param bool $apply false: only list
     * @return array[] each: id, state, action
     */
    public static function purge( $days, $apply )
    {
        $limit = time() - max( 1, (int)$days ) * 86400;
        $done = array();
        foreach ( (array)glob( self::directory() . '/*.json' ) as $file )
        {
            $id = basename( $file, '.json' );
            $data = self::load( $id );
            if ( !$data || (int)$data['created'] >= $limit )
                continue;
            if ( $data['state'] === self::APPLIED )
            {
                $done[] = array( 'id' => $id, 'state' => $data['state'], 'action' => 'discard' );
                if ( $apply )
                    self::discard( $data );
            }
            else if ( $data['state'] !== self::PENDING )
            {
                $done[] = array( 'id' => $id, 'state' => $data['state'], 'action' => 'remove' );
                if ( $apply )
                    @unlink( $file );
            }
        }
        return $done;
    }
}
