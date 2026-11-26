<?php
/**
 * exp:changeclass (ext:expchangeclass:changeclass): changes the class of objects from a shell, with the same plan,
 * restore point and transaction as the administration. A dry run unless --apply.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

namespace Exponential\Command\Extension\Expchangeclass
{

class Changeclass extends \Exponential\Runnable\Command
{
    /** @var bool */
    protected $json = false;

    /** @var array the JSON document */
    protected $document = array();

    public function run()
    {
        $this->script( array(
            'description' => "Change the class of objects, mapping each attribute of the new class onto one of the old, with the\n"
                . "same preview, restore point and single transaction as the administration (changeclass/map_attributes).\n"
                . "Without --apply nothing is written: the plan of each object is printed (what is carried over,\n"
                . "converted, copied, left empty or dropped, per language, and what refuses the change).\n\n"
                . "  --object=<id|remote:...>   the object, may be repeated\n"
                . "  --from=<class> [--subtree=<node>] [--limit=<n>]   every published object of a class (below a node)\n"
                . "  --class=<identifier>       the class they become\n"
                . "  --map=<new>=<old>          an attribute of the new class takes the content of an old one (may be\n"
                . "                             repeated; --map=<new>= leaves it empty); missing: the proposed mapping\n"
                . "  --restore=<id>             undo a change from its restore point (with --apply)\n"
                . "  --discard=<id>             discard a restore point: the dropped data goes for good (with --apply)\n"
                . "  --list                     the recent changes and their restore points\n"
                . "  --purge=<days>             discard restore points older than that (with --apply)\n"
                . "  --user=<login>             act as this user (default: migration.ini [RunSettings] RunAsUser, else admin)\n"
                . "  --format=json              the plan and results as JSON\n\n"
                . "Exit codes: 0 done (or nothing refused in a dry run), 1 refused or failed, 2 usage error.\n\n"
                . "Example: ./console exp:changeclass --object=57 --class=landing_page --map=title=name\n"
                . "         ./console exp:changeclass --from=article --class=blog_post --subtree=2 --apply\n"
                . "         ./console exp:changeclass --restore=20261103-101500-57-a1b2c3 --apply",
            'use-session' => false,
            'use-modules' => true,
            'use-extensions' => true
        ) );
        $options = $this->startup(
            '[apply][dry-run][object:*][from:][subtree:][limit:][class:][map:*][restore:][discard:][list][purge:][user:][format:]',
            '',
            array(
                'apply' => 'Write the changes (the default is a dry run)',
                'dry-run' => 'The default, kept for scripts that pass it',
                'object' => 'An object (id, remote:<remote id>), may be repeated',
                'from' => 'Every published object of this class (identifier or id)',
                'subtree' => 'With --from: only below this node (default 1, the whole tree)',
                'limit' => 'With --from: at most this many objects',
                'class' => 'The class the objects become (identifier or id)',
                'map' => '<new attribute>=<old attribute>, may be repeated',
                'restore' => 'Undo the change of this restore point',
                'discard' => 'Discard this restore point',
                'list' => 'List the recent changes',
                'purge' => 'Discard restore points older than this many days',
                'user' => 'Act as this user (login)',
                'format' => 'text (default) or json',
            )
        );
        $apply = self::applyRequested( $options );
        $this->json = isset( $options['format'] ) && $options['format'] === 'json';
        if ( $apply === null )
            return $this->usage( '--apply and --dry-run exclude each other; without --apply nothing is changed' );
        if ( !$this->actAs( isset( $options['user'] ) ? (string)$options['user'] : '' ) )
            return $this->finish( 1 );
        $this->document = array( 'apply' => $apply, 'objects' => array() );

        if ( !empty( $options['list'] ) )
            return $this->listPoints();
        if ( isset( $options['purge'] ) && $options['purge'] !== null && $options['purge'] !== false )
            return $this->purge( (int)$options['purge'], $apply );
        if ( !empty( $options['restore'] ) )
            return $this->restore( (string)$options['restore'], $apply );
        if ( !empty( $options['discard'] ) )
            return $this->discard( (string)$options['discard'], $apply );

        if ( empty( $options['class'] ) )
            return $this->usage( '--class is missing (the class the objects become)' );
        $dest = \expChangeClassService::fetchClass( (string)$options['class'] );
        if ( !$dest )
            return $this->fail( "no class '{$options['class']}'" );

        $mapping = null;
        if ( !empty( $options['map'] ) )
        {
            $mapping = array();
            foreach ( (array)$options['map'] as $pair )
            {
                if ( strpos( (string)$pair, '=' ) === false )
                    return $this->usage( "--map=$pair is not <new>=<old>" );
                list( $to, $from ) = explode( '=', (string)$pair, 2 );
                $mapping[trim( $to )] = trim( $from );
            }
        }

        $objects = array();
        foreach ( !empty( $options['object'] ) ? (array)$options['object'] : array() as $value )
        {
            $id = ctype_digit( (string)$value ) ? (int)$value
                : ( strpos( (string)$value, 'remote:' ) === 0 ? $this->objectByRemoteID( substr( (string)$value, 7 ) ) : 0 );
            if ( !$id || !\eZContentObject::fetch( $id ) )
                return $this->fail( "no object '$value'" );
            $objects[] = $id;
        }
        if ( !empty( $options['from'] ) )
        {
            $source = \expChangeClassService::fetchClass( (string)$options['from'] );
            if ( !$source )
                return $this->fail( "no class '{$options['from']}'" );
            $node = \eZContentObjectTreeNode::fetch( !empty( $options['subtree'] ) ? (int)$options['subtree'] : 1 );
            if ( !$node )
                return $this->fail( "no node '{$options['subtree']}'" );
            $objects = array_merge( $objects, \expChangeClassService::objectsInSubtree( (int)$source->attribute( 'id' ), (string)$node->attribute( 'path_string' ),
                                                                                       !empty( $options['limit'] ) ? (int)$options['limit'] : 0 ) );
        }
        if ( !$objects )
            return $this->usage( 'no objects: give --object or --from' );

        $refused = 0;
        $changed = 0;
        foreach ( array_values( array_unique( $objects ) ) as $objectID )
        {
            $plan = \expChangeClassService::preview( $objectID, $dest, $mapping );
            $entry = $this->describe( $objectID, $plan );
            if ( !$plan['ok'] )
            {
                $refused++;
                $this->line( 'REFUSED     object ' . $objectID . ': ' . implode( '; ', array_map( array( 'expChangeClassPlanner', 'render' ), $plan['errors'] ) ) );
            }
            else if ( $apply )
            {
                $result = \expChangeClassService::apply( $objectID, $dest, $plan['mapping'], array( 'via' => 'command', 'token' => $plan['token'] ) );
                $entry['result'] = array( 'ok' => $result['ok'], 'error' => $result['error'], 'restore_point' => $result['restore_point'], 'ms' => $result['ms'] );
                if ( $result['ok'] )
                {
                    $changed++;
                    $this->line( 'changed     object ' . $objectID . ' (restore point ' . $result['restore_point'] . ', ' . $result['ms'] . ' ms)' );
                }
                else
                {
                    $refused++;
                    $this->line( 'FAILED      object ' . $objectID . ': ' . $result['error'] );
                }
            }
            $this->document['objects'][] = $entry;
        }

        $total = count( $this->document['objects'] );
        if ( $apply )
            $summary = "$changed of $total objects changed into " . $dest->attribute( 'identifier' ) . ( $refused ? ", $refused refused or failed (unchanged)" : '' );
        else
            $summary = ( $total - $refused ) . " of $total objects can be changed into " . $dest->attribute( 'identifier' ) . ( $refused ? ", $refused refused" : '' ) . ' (dry run, nothing changed; --apply writes)';
        return $this->finish( $refused ? 1 : 0, $summary );
    }

    /**
     * The plan of one object, printed (text) and returned (for JSON).
     */
    protected function describe( $objectID, array $plan )
    {
        $entry = array( 'object_id' => (int)$objectID, 'ok' => $plan['ok'], 'mapping' => $plan['mapping'],
                        'errors' => array_map( array( 'expChangeClassPlanner', 'render' ), $plan['errors'] ),
                        'warnings' => array_map( array( 'expChangeClassPlanner', 'render' ), $plan['warnings'] ),
                        'rows' => array(), 'dropped' => array(), 'stats' => $plan['stats'], 'token' => $plan['token'] );
        if ( isset( $plan['facts']['source_class'] ) )
        {
            $entry['name'] = $plan['facts']['object']['name'];
            $entry['from'] = $plan['facts']['source_class']['identifier'];
            $entry['into'] = $plan['facts']['dest_class']['identifier'];
            $this->line( sprintf( 'object %d   %s: %s -> %s, %d versions, %s', $objectID, $entry['name'], $entry['from'], $entry['into'],
                                  $plan['stats']['versions'], implode( ', ', $plan['facts']['object']['languages'] ) ) );
        }
        foreach ( $plan['rows'] as $row )
        {
            $entry['rows'][] = array( 'identifier' => $row['identifier'], 'datatype' => $row['datatype'], 'source' => $row['source'],
                                      'source_datatype' => $row['source_datatype'], 'kind' => $row['kind'], 'converter' => $row['converter'] );
            $this->line( sprintf( '  %-24s <- %-24s %s', $row['identifier'] . ' (' . $row['datatype'] . ')',
                                  $row['source'] !== '' ? $row['source'] . ' (' . $row['source_datatype'] . ')' : '-', $row['kind'] ) );
        }
        foreach ( $plan['dropped'] as $dropped )
        {
            $entry['dropped'][] = array( 'identifier' => $dropped['identifier'], 'datatype' => $dropped['datatype'], 'languages_with_content' => $dropped['languages'] );
            $this->line( sprintf( '  drops %-18s %s', $dropped['identifier'], $dropped['languages'] ? 'content in ' . implode( ', ', $dropped['languages'] ) . ' (kept in the restore point)' : 'empty' ) );
        }
        foreach ( $entry['warnings'] as $warning )
            $this->line( '  WARN ' . $warning );
        return $entry;
    }

    protected function listPoints()
    {
        $list = \expChangeClassRestorePoint::listRecent( 50 );
        foreach ( $list as $point )
        {
            $this->line( sprintf( '%s  %-9s object %d %s: %s -> %s by %s', $point['id'], $point['state'], $point['object']['id'], $point['object']['name'],
                                  $point['source_class']['identifier'], $point['dest_class']['identifier'], $point['user_login'] ) );
        }
        $this->document['restore_points'] = $list;
        return $this->finish( 0, count( $list ) . ' restore points in ' . \expChangeClassRestorePoint::directory() );
    }

    protected function restore( $id, $apply )
    {
        $data = \expChangeClassRestorePoint::load( $id );
        if ( !$data )
            return $this->fail( "no restore point '$id'" );
        $problem = \expChangeClassRestorePoint::restoreProblem( $data );
        $this->document['restore_point'] = \expChangeClassRestorePoint::summary( $data );
        if ( $problem !== null )
            return $this->fail( $problem );
        if ( !$apply )
            return $this->finish( 0, 'object ' . $data['object']['id'] . ' can be restored to ' . $data['source_class']['identifier'] . ' (dry run, nothing changed; --apply restores)' );
        $error = null;
        if ( !\expChangeClassRestorePoint::restore( $data, $error ) )
            return $this->fail( (string)$error );
        \expChangeClassService::auditRestorePoint( 'content.expchangeclass_object.restore', $data );
        return $this->finish( 0, 'object ' . $data['object']['id'] . ' is of the class ' . $data['source_class']['identifier'] . ' again' );
    }

    protected function discard( $id, $apply )
    {
        $data = \expChangeClassRestorePoint::load( $id );
        if ( !$data )
            return $this->fail( "no restore point '$id'" );
        if ( $data['state'] !== \expChangeClassRestorePoint::APPLIED )
            return $this->fail( "restore point $id is {$data['state']}; only an applied change has a restore point to discard" );
        if ( !$apply )
            return $this->finish( 0, "restore point $id would be discarded (dry run; --apply discards)" );
        \expChangeClassRestorePoint::discard( $data );
        \expChangeClassService::auditRestorePoint( 'content.expchangeclass_restorepoint.discard', $data );
        return $this->finish( 0, "restore point $id discarded" );
    }

    protected function purge( $days, $apply )
    {
        if ( $days < 1 )
            return $this->usage( '--purge needs a number of days' );
        $done = \expChangeClassRestorePoint::purge( $days, $apply );
        foreach ( $done as $entry )
            $this->line( ( $apply ? '' : 'would ' ) . $entry['action'] . ' ' . $entry['id'] . ' (' . $entry['state'] . ')' );
        $this->document['purged'] = $done;
        return $this->finish( 0, count( $done ) . " restore points older than $days days " . ( $apply ? 'purged' : 'would be purged (dry run; --apply purges)' ) );
    }

    protected function objectByRemoteID( $remoteID )
    {
        $object = \eZContentObject::fetchByRemoteID( $remoteID );
        return $object ? (int)$object->attribute( 'id' ) : 0;
    }

    /**
     * Logs in as the user the command acts as.
     */
    protected function actAs( $login )
    {
        if ( $login === '' )
        {
            $ini = \eZINI::instance( 'migration.ini' );
            $login = $ini->hasVariable( 'RunSettings', 'RunAsUser' ) ? trim( (string)$ini->variable( 'RunSettings', 'RunAsUser' ) ) : '';
            if ( $login === '' )
                $login = 'admin';
        }
        $user = \eZUser::fetchByName( $login );
        if ( !$user instanceof \eZUser )
        {
            $this->fail( "no user '$login' (--user)" );
            return false;
        }
        \eZUser::setCurrentlyLoggedInUser( $user, (int)$user->attribute( 'contentobject_id' ), \eZUser::NO_SESSION_REGENERATE );
        $this->document['user'] = $login;
        return true;
    }

    protected function line( $text )
    {
        if ( !$this->json )
            $this->output( $text );
    }

    protected function usage( $text )
    {
        $this->document['error'] = $text;
        if ( $this->json )
            $this->output( json_encode( array( 'ok' => false, 'error' => $text ) ) );
        else
            $this->error( 'FAIL: ' . $text );
        $this->shutdown( 2 );
        return 2;
    }

    protected function fail( $text )
    {
        $this->document['error'] = $text;
        return $this->finish( 1, $text );
    }

    protected function finish( $code, $summary = '' )
    {
        if ( $this->json )
        {
            $this->document['ok'] = $code === 0;
            $this->document['summary'] = $summary;
            $this->output( json_encode( $this->document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
        }
        else if ( $summary !== '' )
        {
            $this->output();
            if ( $code === 0 )
                $this->output( 'PASS: ' . $summary );
            else
                $this->error( 'FAIL: ' . $summary );
        }
        $this->shutdown( $code );
        return $code;
    }
}

}
