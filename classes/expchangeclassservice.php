<?php
/**
 * File containing the expChangeClassService class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * A class change from preview to result, the same for the administration, the command, the background job and the
 * migration step:
 *
 *   1. preview(): the facts of the object (versions, languages, drafts, lock, relations, running jobs, the user's
 *      access) and the plan of expChangeClassPlanner, with a token of what was previewed;
 *   2. apply(): the plan again (refused on any error, and when a token is given that no longer matches: the object
 *      changed since the preview), the restore point written (no restore point, no change), the change in one
 *      transaction (conversionFunctions::convertObject(), rolled back as a unit on any error), then the caches of the
 *      object (content view, template block), its search index entry and the audit;
 *   3. expChangeClassRestorePoint::restore() puts it back.
 *
 * Nothing here prints or redirects; the callers show the result.
 */
class expChangeClassService
{
    /**
     * The rules of changeclass.ini.
     *
     * @return array
     */
    public static function rules()
    {
        return expChangeClassPlanner::rulesFromSettings( eZINI::instance( 'changeclass.ini' ) );
    }

    /**
     * A class by id or identifier.
     *
     * @param int|string $class
     * @return eZContentClass|null
     */
    public static function fetchClass( $class )
    {
        if ( $class instanceof eZContentClass )
            return $class;
        $found = is_numeric( $class ) ? eZContentClass::fetch( (int)$class ) : eZContentClass::fetchByIdentifier( (string)$class );
        return $found instanceof eZContentClass ? $found : null;
    }

    /**
     * The attributes of a class as the planner reads them.
     *
     * @param eZContentClass $class
     * @return array
     */
    public static function classFacts( $class )
    {
        $attributes = array();
        foreach ( (array)$class->dataMap() as $identifier => $attribute )
        {
            $attributes[] = array( 'id' => (int)$attribute->attribute( 'id' ), 'identifier' => (string)$identifier,
                                   'name' => (string)$attribute->attribute( 'name' ) !== '' ? (string)$attribute->attribute( 'name' ) : (string)$identifier,
                                   'datatype' => (string)$attribute->attribute( 'data_type_string' ),
                                   'required' => (bool)$attribute->attribute( 'is_required' ),
                                   'translatable' => (bool)$attribute->attribute( 'can_translate' ) );
        }
        $name = (string)$class->attribute( 'name' );
        return array( 'id' => (int)$class->attribute( 'id' ), 'identifier' => (string)$class->attribute( 'identifier' ),
                      'name' => $name !== '' ? $name : (string)$class->attribute( 'identifier' ),
                      'is_container' => (bool)$class->attribute( 'is_container' ), 'attributes' => $attributes );
    }

    /**
     * The facts of an object for the planner.
     *
     * @param eZContentObject $object
     * @param eZContentClass $sourceClass
     * @param eZContentClass $destClass
     * @return array
     */
    public static function facts( $object, $sourceClass, $destClass, $ignoreJobID = null )
    {
        $db = eZDB::instance();
        $objectID = (int)$object->attribute( 'id' );
        // every language a row exists for, in any version (eZContentObject::languages() keeps only the siteaccess's)
        $languages = array();
        foreach ( (array)$db->arrayQuery( "SELECT DISTINCT language_code FROM ezcontentobject_attribute WHERE contentobject_id = $objectID ORDER BY language_code" ) as $row )
            $languages[] = (string)$row['language_code'];
        $versions = array();
        foreach ( (array)$db->arrayQuery( "SELECT version, status, creator_id FROM ezcontentobject_version WHERE contentobject_id = $objectID ORDER BY version" ) as $row )
            $versions[] = array( 'version' => (int)$row['version'], 'status' => (int)$row['status'], 'creator_id' => (int)$row['creator_id'] );

        $isUser = false;
        foreach ( (array)$sourceClass->dataMap() as $attribute )
        {
            if ( $attribute->attribute( 'data_type_string' ) === 'ezuser' )
                $isUser = true;
        }
        $locked = false;
        if ( method_exists( $object, 'stateIdentifierArray' ) )
            $locked = in_array( 'ez_lock/locked', (array)$object->stateIdentifierArray(), true );

        // what each source attribute holds now, per language (current version)
        $values = array();
        foreach ( $languages as $language )
        {
            foreach ( (array)$object->fetchDataMap( false, $language ) as $identifier => $attribute )
            {
                $text = '';
                $has = false;
                try
                {
                    $has = (bool)$attribute->hasContent();
                    if ( $has )
                        $text = self::shorten( (string)$attribute->toString() );
                }
                catch ( Throwable $e )
                {
                    $text = '';
                }
                $values[$identifier][$language] = array( 'has_content' => $has, 'text' => $text );
            }
        }

        $reverse = $db->arrayQuery( "SELECT COUNT( DISTINCT from_contentobject_id ) AS count FROM ezcontentobject_link
                                     WHERE to_contentobject_id = $objectID AND contentclassattribute_id <> 0 AND from_contentobject_id <> $objectID" );
        $job = '';
        if ( class_exists( 'expContentJobLock' ) )
        {
            $nodeIDs = array();
            foreach ( (array)$object->assignedNodes( false ) as $node )
                $nodeIDs[] = (int)$node['node_id'];
            try
            {
                $running = $nodeIDs ? expContentJobLock::checkNodes( $nodeIDs, $ignoreJobID ) : false;
                if ( $running )
                    $job = (string)$running->description();
            }
            catch ( Throwable $e )
            {
                $job = '';
            }
        }

        $mainNode = $object->mainNode();
        return array(
            'source_class' => self::classFacts( $sourceClass ),
            'dest_class' => self::classFacts( $destClass ),
            'object' => array( 'id' => $objectID, 'name' => (string)$object->attribute( 'name' ), 'status' => (int)$object->attribute( 'status' ),
                               'is_user' => $isUser, 'locked' => $locked,
                               'children_count' => $mainNode ? (int)$mainNode->attribute( 'children_count' ) : 0,
                               'languages' => $languages, 'versions' => $versions, 'current_user_id' => (int)eZUser::currentUserID() ),
            'values' => $values,
            'reverse_relations' => isset( $reverse[0]['count'] ) ? (int)$reverse[0]['count'] : 0,
            'job' => $job,
            'can_edit' => (bool)$object->canEdit(),
        );
    }

    /**
     * A value cut for the preview: one line, at most 90 characters.
     */
    public static function shorten( $text )
    {
        $text = trim( preg_replace( '/\s+/u', ' ', strip_tags( (string)$text ) ) );
        return mb_strlen( $text ) > 90 ? mb_substr( $text, 0, 89 ) . "\u{2026}" : $text;
    }

    /**
     * The preview of a class change.
     *
     * @param int|eZContentObject $object
     * @param int|string $destClass id or identifier
     * @param array|null $mapping null: the proposed mapping
     * @param array $options ignore_job: the id of a content job whose lock does not count (the job doing the change)
     * @return array ok, errors, warnings, rows, dropped, stats, mapping, token, object, source_class, dest_class
     *               (the planner's plan with the objects), or ok false and errors when something is missing
     */
    public static function preview( $object, $destClass, $mapping = null, array $options = array() )
    {
        if ( !$object instanceof eZContentObject )
            $object = eZContentObject::fetch( (int)$object );
        if ( !$object instanceof eZContentObject )
            return self::refusal( 'no_object', 'The object does not exist.' );
        $sourceClass = eZContentClass::fetch( (int)$object->attribute( 'contentclass_id' ) );
        if ( !$sourceClass instanceof eZContentClass )
            return self::refusal( 'no_source_class', 'The class of the object does not exist.' );
        $destClass = self::fetchClass( $destClass );
        if ( !$destClass instanceof eZContentClass )
            return self::refusal( 'no_dest_class', 'The new class does not exist.' );

        $rules = self::rules();
        $facts = self::facts( $object, $sourceClass, $destClass, isset( $options['ignore_job'] ) ? (string)$options['ignore_job'] : null );
        if ( $mapping === null )
            $mapping = expChangeClassPlanner::proposeMapping( $facts['source_class']['attributes'], $facts['dest_class']['attributes'], $rules );
        $plan = expChangeClassPlanner::plan( $facts, (array)$mapping, $rules );
        if ( !expChangeClassAccess::can( 'convert', $object, (int)$destClass->attribute( 'id' ) ) )
        {
            $plan['errors'][] = expChangeClassPlanner::message( 'no_access', 'Your role does not allow changing %class into %new_class here (policy changeclass/convert).',
                                                                array( '%class' => $facts['source_class']['name'], '%new_class' => $facts['dest_class']['name'] ) );
            $plan['ok'] = false;
        }
        $plan['candidates'] = array();
        foreach ( $facts['dest_class']['attributes'] as $attribute )
            $plan['candidates'][$attribute['identifier']] = expChangeClassPlanner::candidates( $attribute, $facts['source_class']['attributes'], $rules );
        $plan['facts'] = $facts;
        $plan['object'] = $object;
        $plan['source_class'] = $sourceClass;
        $plan['dest_class'] = $destClass;
        $plan['token'] = self::token( $object, $sourceClass, $destClass, $plan['mapping'] );
        return $plan;
    }

    /**
     * @return array a plan that refuses
     */
    protected static function refusal( $code, $text )
    {
        return array( 'ok' => false, 'errors' => array( expChangeClassPlanner::message( $code, $text ) ), 'warnings' => array(),
                      'rows' => array(), 'dropped' => array(), 'stats' => array(), 'mapping' => array(), 'candidates' => array(),
                      'token' => '', 'object' => null, 'source_class' => null, 'dest_class' => null, 'facts' => array() );
    }

    /**
     * The token of a plan.
     */
    public static function token( $object, $sourceClass, $destClass, array $mapping )
    {
        return expChangeClassPlanner::token( array( 'object_id' => $object->attribute( 'id' ), 'modified' => $object->attribute( 'modified' ),
                                                    'current_version' => $object->attribute( 'current_version' ),
                                                    'source_class_id' => $sourceClass->attribute( 'id' ), 'dest_class_id' => $destClass->attribute( 'id' ),
                                                    'mapping' => $mapping ) );
    }

    /**
     * Changes the class of an object.
     *
     * @param int|eZContentObject $object
     * @param int|string $destClass
     * @param array|null $mapping null: the proposed mapping
     * @param array $options token (must match the preview's), via (admin, command, job, migration), job_id
     * @return array ok, error (English), errors (messages), plan, restore_point (the id), ms
     */
    public static function apply( $object, $destClass, $mapping = null, array $options = array() )
    {
        $start = microtime( true );
        $plan = self::preview( $object, $destClass, $mapping, $options );
        $result = array( 'ok' => false, 'error' => '', 'errors' => $plan['errors'], 'plan' => $plan, 'restore_point' => '', 'ms' => 0 );
        if ( !$plan['ok'] )
        {
            $result['error'] = expChangeClassPlanner::render( $plan['errors'][0] );
            self::audit( 'content.expchangeclass_object.convert', $plan, array( 'result' => 'refused', 'reason' => $plan['errors'][0]['code'] ) );
            return $result;
        }
        if ( isset( $options['token'] ) && $options['token'] !== '' && $options['token'] !== $plan['token'] )
        {
            $result['errors'] = array( expChangeClassPlanner::message( 'stale', 'The object or the mapping changed since the preview. Check the new preview and apply again.' ) );
            $result['error'] = expChangeClassPlanner::render( $result['errors'][0] );
            $result['stale'] = true;
            return $result;
        }

        $object = $plan['object'];
        $restorePoint = expChangeClassRestorePoint::create( $object, $plan['source_class'], $plan['dest_class'], $plan['mapping'], $options );
        if ( !$restorePoint )
        {
            $result['errors'] = array( expChangeClassPlanner::message( 'no_restore_point', 'The restore point could not be written to %dir, so nothing was changed.',
                                                                      array( '%dir' => expChangeClassRestorePoint::directory() ) ) );
            $result['error'] = expChangeClassPlanner::render( $result['errors'][0] );
            self::audit( 'content.expchangeclass_object.convert', $plan, array( 'result' => 'failed', 'error' => array( 'message' => $result['error'] ) ) );
            return $result;
        }
        $result['restore_point'] = $restorePoint['id'];

        $error = null;
        require_once __DIR__ . '/functions.php';
        $converted = false;
        try
        {
            $converted = conversionFunctions::convertObject( (int)$object->attribute( 'id' ), (int)$plan['dest_class']->attribute( 'id' ), $plan['mapping'], $error );
        }
        catch ( Throwable $e )
        {
            // convertObject() rolls back what it began; anything thrown past it is reported, not passed on
            $converted = false;
            $error = $e->getMessage();
            if ( eZDB::instance()->transactionCounter() > 0 )
                eZDB::instance()->rollback();
        }
        if ( !$converted )
        {
            expChangeClassRestorePoint::mark( $restorePoint, expChangeClassRestorePoint::FAILED, array( 'error' => (string)$error, 'attributes' => array(), 'links' => array() ) );
            $result['errors'] = array( expChangeClassPlanner::message( 'rolled_back', 'The change was rolled back, nothing was changed: %reason', array( '%reason' => (string)$error ) ) );
            $result['error'] = expChangeClassPlanner::render( $result['errors'][0] );
            self::audit( 'content.expchangeclass_object.convert', $plan, array( 'result' => 'failed', 'error' => array( 'ref' => $restorePoint['id'], 'message' => (string)$error ) ) );
            return $result;
        }

        $objectID = (int)$object->attribute( 'id' );
        expChangeClassRestorePoint::mark( $restorePoint, expChangeClassRestorePoint::APPLIED, array(
            'after' => array( 'versions' => expChangeClassRestorePoint::versions( $objectID ), 'time' => time() ) ) );
        self::afterChange( $objectID );
        $result['ok'] = true;
        $result['ms'] = (int)round( ( microtime( true ) - $start ) * 1000 );
        self::audit( 'content.expchangeclass_object.convert', $plan, array( 'restore_point' => $restorePoint['id'] ) );
        return $result;
    }

    /**
     * After the class of an object changed (or was restored): the in-memory caches, the content view and template
     * block caches of its nodes, and its search index entry.
     *
     * @param int $objectID
     */
    public static function afterChange( $objectID )
    {
        $objectID = (int)$objectID;
        eZContentObject::clearCache( array( $objectID ) );
        unset( $GLOBALS['eZContentObjectDataMapCache'][$objectID], $GLOBALS['eZContentObjectContentObjectCache'][$objectID] );
        eZContentCacheManager::clearContentCache( $objectID );
        eZContentCacheManager::clearTemplateBlockCache( $objectID );
        $object = eZContentObject::fetch( $objectID );
        if ( $object instanceof eZContentObject && (int)$object->attribute( 'status' ) === eZContentObject::STATUS_PUBLISHED )
        {
            try
            {
                eZSearch::removeObjectById( $objectID );
                eZSearch::addObject( $object, true );
            }
            catch ( Throwable $e )
            {
                // the change stands; the cronjob's indexing or exp:updatesearchindex catches up
                eZDebug::writeWarning( "Search index of object $objectID: " . $e->getMessage(), __METHOD__ );
            }
        }
    }

    /**
     * Records an event of the extension in the audit (Exponential 6.0.15 and later; nothing without it).
     *
     * @param string $name
     * @param array $plan the plan (preview())
     * @param array $data more data: result, reason, error, restore_point
     */
    public static function audit( $name, array $plan, array $data = array() )
    {
        if ( !class_exists( 'expAudit' ) )
            return;
        try
        {
            $object = isset( $plan['object'] ) && $plan['object'] instanceof eZContentObject ? $plan['object'] : null;
            $event = array();
            if ( $object )
                $event['object'] = array( 'type' => 'object', 'id' => (int)$object->attribute( 'id' ), 'name' => (string)$object->attribute( 'name' ) );
            if ( isset( $plan['facts']['source_class'] ) )
            {
                $event['before'] = array( 'class' => $plan['facts']['source_class']['identifier'] );
                $event['after'] = array( 'class' => $plan['facts']['dest_class']['identifier'] );
            }
            foreach ( array( 'result', 'reason', 'error' ) as $key )
                if ( isset( $data[$key] ) )
                    $event[$key] = $data[$key];
            $mapping = isset( $plan['mapping'] ) ? $plan['mapping'] : array();
            $event['x'] = array( 'expchangeclass' => array( 'mapping' => $mapping,
                                                            'restore_point' => isset( $data['restore_point'] ) ? $data['restore_point'] : '',
                                                            'dropped' => isset( $plan['dropped'] ) ? count( $plan['dropped'] ) : 0 ) );
            expAudit::event( $name, $event );
        }
        catch ( Throwable $e )
        {
            eZDebug::writeWarning( 'Audit: ' . $e->getMessage(), __METHOD__ );
        }
    }

    /**
     * Records a restore or a discard in the audit.
     *
     * @param string $name
     * @param array $restorePoint
     * @param array $data
     */
    public static function auditRestorePoint( $name, array $restorePoint, array $data = array() )
    {
        if ( !class_exists( 'expAudit' ) )
            return;
        try
        {
            $event = array( 'object' => array( 'type' => 'object', 'id' => (int)$restorePoint['object']['id'], 'name' => (string)$restorePoint['object']['name'] ),
                            'before' => array( 'class' => $restorePoint['dest_class']['identifier'] ),
                            'after' => array( 'class' => $restorePoint['source_class']['identifier'] ),
                            'x' => array( 'expchangeclass' => array( 'restore_point' => $restorePoint['id'] ) ) );
            expAudit::event( $name, array_merge( $event, $data ) );
        }
        catch ( Throwable $e )
        {
            eZDebug::writeWarning( 'Audit: ' . $e->getMessage(), __METHOD__ );
        }
    }

    /**
     * The objects of a class in a subtree (the bulk change's list).
     *
     * @param int $classID
     * @param string $pathString the subtree's path, e.g. /1/2/
     * @param int $limit
     * @param int[] $exclude object ids to leave out
     * @return int[] object ids, sorted
     */
    public static function objectsInSubtree( $classID, $pathString, $limit = 0, array $exclude = array() )
    {
        $db = eZDB::instance();
        $sql = 'SELECT DISTINCT o.id FROM ezcontentobject o, ezcontentobject_tree t
                WHERE t.contentobject_id = o.id AND o.contentclass_id = ' . (int)$classID . " AND o.status = 1
                  AND t.path_string LIKE '" . $db->escapeString( $pathString ) . "%'";
        if ( $exclude )
            $sql .= ' AND o.id NOT IN (' . implode( ', ', array_map( 'intval', $exclude ) ) . ')';
        $sql .= ' ORDER BY o.id';
        $rows = $limit > 0 ? $db->arrayQuery( $sql, array( 'limit' => (int)$limit ) ) : $db->arrayQuery( $sql );
        $ids = array();
        foreach ( (array)$rows as $row )
            $ids[] = (int)$row['id'];
        return $ids;
    }

    /**
     * How many objects of a class a subtree holds.
     */
    public static function countInSubtree( $classID, $pathString )
    {
        $db = eZDB::instance();
        $rows = $db->arrayQuery( 'SELECT COUNT( DISTINCT o.id ) AS count FROM ezcontentobject o, ezcontentobject_tree t
                                  WHERE t.contentobject_id = o.id AND o.contentclass_id = ' . (int)$classID . " AND o.status = 1
                                    AND t.path_string LIKE '" . $db->escapeString( $pathString ) . "%'" );
        return isset( $rows[0]['count'] ) ? (int)$rows[0]['count'] : 0;
    }
}
