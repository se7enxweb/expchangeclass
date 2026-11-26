<?php
/**
 * changeclass/bulk[/<ClassID>/<NewClassID>/<NodeID>]: every published object of a class in a subtree changed into
 * another class with one mapping, as a background content job (Exponential 6.0.15 and later) with its progress page
 * content/job/<id>. Each object gets its own plan, restore point and transaction; an object that refuses is skipped
 * with the reason.
 *
 * POST (303):
 *   ChooseButton (SourceClassID, DestinationClassID, SubtreeNodeID)  the classes and subtree, as view parameters
 *   PreviewButton (SourceAttribute[])                               the mapping, kept for the page
 *   StartButton (SourceAttribute[], PlanToken)                      the job; then content/job/<id>.
 *                                                                   "Verify it's you" asks first.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class Bulk extends \Exponential\Runnable\ModuleView
{
    const SAMPLE = 10;

    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        $classID = isset( $Params['ClassID'] ) ? (int)$Params['ClassID'] : 0;
        $newClassID = isset( $Params['NewClassID'] ) ? (int)$Params['NewClassID'] : 0;
        $nodeID = isset( $Params['NodeID'] ) ? (int)$Params['NodeID'] : 0;

        if ( $http->hasPostVariable( 'ChooseButton' ) )
        {
            return \expChangeClassView::redirect( $Module, 'changeclass/bulk/' . (int)$http->postVariable( 'SourceClassID' ) . '/'
                                                          . (int)$http->postVariable( 'DestinationClassID' ) . '/'
                                                          . max( 1, (int)$http->postVariable( 'SubtreeNodeID' ) ) );
        }
        $here = 'changeclass/bulk/' . $classID . '/' . $newClassID . '/' . $nodeID;
        $key = 'bulk/' . $classID . '/' . $newClassID;

        $source = $classID ? \eZContentClass::fetch( $classID ) : null;
        $dest = $newClassID ? \eZContentClass::fetch( $newClassID ) : null;
        $node = $nodeID ? \eZContentObjectTreeNode::fetch( $nodeID ) : null;
        $chosen = $source instanceof \eZContentClass && $dest instanceof \eZContentClass && $node instanceof \eZContentObjectTreeNode;

        $posted = \expChangeClassView::postedMapping();
        if ( $chosen && $posted !== null )
        {
            \expChangeClassView::keepMapping( $key, $posted );
            if ( $http->hasPostVariable( 'StartButton' ) )
            {
                $plan = self::plan( $source, $dest, $posted );
                $token = self::token( $classID, $newClassID, $nodeID, $plan['mapping'] );
                if ( !$plan['ok'] || !$http->hasPostVariable( 'PlanToken' ) || $http->postVariable( 'PlanToken' ) !== $token )
                {
                    \expChangeClassView::flash( 'warn', \ezpI18n::tr( 'extension/expchangeclass', 'Nothing was started: check the new preview.' ) );
                    return \expChangeClassView::redirect( $Module, $here );
                }
                if ( !class_exists( 'expContentJob' ) )
                {
                    \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'Background jobs need Exponential 6.0.15 or later. Use the command shown below.' ) );
                    return \expChangeClassView::redirect( $Module, $here );
                }
                try
                {
                    $job = \expContentJob::create( 'changeclass', array( 'source_class_id' => $classID, 'dest_class_id' => $newClassID,
                                                                         'mapping' => $plan['mapping'], 'node_id' => $nodeID, 'mode' => 'job' ),
                                                   \eZUser::currentUser() );
                    $job->spawn();
                }
                catch ( \Exception $e )
                {
                    \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'The job was not started.' ), array( $e->getMessage() ) );
                    return \expChangeClassView::redirect( $Module, $here );
                }
                if ( class_exists( 'expAudit' ) )
                    \expAudit::event( 'content.expchangeclass_bulk.start', array( 'object' => array( 'type' => 'node', 'id' => $nodeID, 'name' => (string)$node->attribute( 'name' ) ),
                                                                                  'before' => array( 'class' => $source->attribute( 'identifier' ) ),
                                                                                  'after' => array( 'class' => $dest->attribute( 'identifier' ) ),
                                                                                  'x' => array( 'expchangeclass' => array( 'job' => $job->id(), 'mapping' => $plan['mapping'] ) ) ) );
                return \expChangeClassView::redirect( $Module, 'content/job/' . $job->id() );
            }
            return \expChangeClassView::redirect( $Module, $here );
        }

        $variables = array( 'chosen' => $chosen, 'classes' => \expChangeClassView::classOptions(), 'source_id' => $classID, 'dest_id' => $newClassID,
                            'node_id' => $nodeID ?: 2, 'node' => $node, 'jobs_available' => class_exists( 'expContentJob' ) );
        if ( $chosen )
        {
            $plan = self::plan( $source, $dest, \expChangeClassView::keptMapping( $key ) );
            $access = \eZUser::currentUser()->hasAccessTo( 'changeclass', 'bulk' );
            if ( !\expChangeClassAccess::allows( (array)$access, array( 'class_id' => $classID, 'new_class_id' => $newClassID, 'section_id' => 0,
                                                                         'node_ids' => array(), 'path_strings' => array() ) ) )
            {
                $plan['errors'][] = \expChangeClassPlanner::message( 'no_access', 'Your role does not allow changing %class into %new_class here (policy changeclass/bulk).',
                                                                     array( '%class' => $source->attribute( 'name' ), '%new_class' => $dest->attribute( 'name' ) ) );
                $plan['ok'] = false;
            }
            $path = (string)$node->attribute( 'path_string' );
            $count = \expChangeClassService::countInSubtree( $classID, $path );
            if ( !$count )
            {
                $plan['errors'][] = \expChangeClassPlanner::message( 'nothing', 'There is no published object of the class %class below this node.', array( '%class' => $source->attribute( 'name' ) ) );
                $plan['ok'] = false;
            }
            $sample = array();
            foreach ( \expChangeClassService::objectsInSubtree( $classID, $path, self::SAMPLE ) as $objectID )
            {
                $object = \eZContentObject::fetch( $objectID );
                if ( $object )
                    $sample[] = $object;
            }
            $translated = \expChangeClassView::translatePlan( $plan );
            $variables += array( 'plan' => $plan, 'errors' => $translated['errors'], 'warnings' => $translated['warnings'],
                                 'source_class' => $source, 'dest_class' => $dest, 'count' => $count, 'sample' => $sample,
                                 'token' => self::token( $classID, $newClassID, $nodeID, $plan['mapping'] ), 'form_uri' => $here,
                                 'command' => self::command( $source, $dest, $nodeID, $plan['mapping'] ) );
        }
        return \expChangeClassView::page( 'bulk', $variables, \expChangeClassView::path( \ezpI18n::tr( 'extension/expchangeclass', 'Change many objects' ) ) );
    }

    /**
     * The plan of two classes, without an object: the mapping, the conversions and what is dropped.
     */
    public static function plan( $source, $dest, $mapping )
    {
        $rules = \expChangeClassService::rules();
        $sourceFacts = \expChangeClassService::classFacts( $source );
        $destFacts = \expChangeClassService::classFacts( $dest );
        if ( $mapping === null )
            $mapping = \expChangeClassPlanner::proposeMapping( $sourceFacts['attributes'], $destFacts['attributes'], $rules );
        $facts = array( 'source_class' => $sourceFacts, 'dest_class' => $destFacts,
                        'object' => array( 'id' => 0, 'name' => '', 'status' => 1, 'is_user' => false, 'locked' => false, 'children_count' => 0,
                                           'languages' => array(), 'versions' => array( array( 'version' => 1, 'status' => 1, 'creator_id' => 0 ) ) ),
                        'values' => array(), 'reverse_relations' => 0, 'job' => '', 'can_edit' => true );
        $plan = \expChangeClassPlanner::plan( $facts, (array)$mapping, $rules );
        // per object, not per class
        $plan['errors'] = array_values( array_filter( $plan['errors'], function ( $e ) { return $e['code'] !== 'no_versions'; } ) );
        $plan['ok'] = !$plan['errors'];
        $plan['candidates'] = array();
        foreach ( $destFacts['attributes'] as $attribute )
            $plan['candidates'][$attribute['identifier']] = \expChangeClassPlanner::candidates( $attribute, $sourceFacts['attributes'], $rules );
        $plan['facts'] = $facts;
        return $plan;
    }

    public static function token( $classID, $newClassID, $nodeID, array $mapping )
    {
        ksort( $mapping );
        return substr( sha1( json_encode( array( 'bulk', (int)$classID, (int)$newClassID, (int)$nodeID, $mapping ) ) ), 0, 20 );
    }

    public static function command( $source, $dest, $nodeID, array $mapping )
    {
        $parts = array( './console exp:changeclass', '--from=' . $source->attribute( 'identifier' ), '--class=' . $dest->attribute( 'identifier' ), '--subtree=' . (int)$nodeID );
        foreach ( $mapping as $to => $from )
            $parts[] = '--map=' . $to . '=' . $from;
        return implode( ' ', $parts );
    }
}

}
