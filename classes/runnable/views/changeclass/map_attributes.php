<?php
/**
 * changeclass/map_attributes/<NodeID>/<ClassID>: step 2 of a class change. The mapping (an old attribute for each
 * attribute of the new class) with the preview of what the change does: the data carried over or converted per
 * language, what is dropped, what refuses the change and what an editor should know.
 *
 * POST (always answered with a 303 back to this address or on):
 *   PreviewButton   the posted mapping is kept and previewed
 *   ApplyButton     the change, when the posted token is the preview's (the object or mapping did not change since):
 *                   restore point, one transaction, caches, search index, audit; then changeclass/result/<id>.
 *                   "Verify it's you" asks first (site.ini [ReauthGroup_changeclass]).
 *   BackButton      back to the class choice
 *   CancelButton    back to the object
 * The form of the earlier versions (SourceObjectID, SourceNodeID, DestinationClassID) is sent on to the address.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class MapAttributes extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        $nodeID = isset( $Params['NodeID'] ) ? (int)$Params['NodeID'] : 0;
        $classID = isset( $Params['ClassID'] ) ? (int)$Params['ClassID'] : 0;
        // the form of the earlier versions posted here without view parameters
        if ( !$nodeID && $http->hasPostVariable( 'SourceNodeID' ) && $http->hasPostVariable( 'DestinationClassID' ) )
            return \expChangeClassView::redirect( $Module, 'changeclass/map_attributes/' . (int)$http->postVariable( 'SourceNodeID' ) . '/' . (int)$http->postVariable( 'DestinationClassID' ) );

        $node = \eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node instanceof \eZContentObjectTreeNode || !$node->attribute( 'can_read' ) )
            return $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        $object = $node->attribute( 'object' );
        $here = 'changeclass/map_attributes/' . $nodeID . '/' . $classID;
        $key = $nodeID . '/' . $classID;

        if ( $http->hasPostVariable( 'CancelButton' ) )
            return \expChangeClassView::redirect( $Module, $node->attribute( 'url_alias' ) );
        if ( $http->hasPostVariable( 'BackButton' ) )
            return \expChangeClassView::redirect( $Module, 'changeclass/select_class/' . $nodeID );

        $posted = \expChangeClassView::postedMapping();
        if ( $posted !== null && $http->hasPostVariable( 'ApplyButton' ) )
        {
            \expChangeClassView::keepMapping( $key, $posted );
            $token = $http->hasPostVariable( 'PlanToken' ) ? (string)$http->postVariable( 'PlanToken' ) : '';
            if ( $token === '' )
            {
                \expChangeClassView::flash( 'warn', \ezpI18n::tr( 'extension/expchangeclass', 'Preview the change before applying it.' ) );
                return \expChangeClassView::redirect( $Module, $here );
            }
            $result = \expChangeClassService::apply( $object, $classID, $posted, array( 'token' => $token, 'via' => 'admin' ) );
            if ( $result['ok'] )
            {
                \expChangeClassView::flash( 'ok', \ezpI18n::tr( 'extension/expchangeclass', 'The class was changed.' ) );
                return \expChangeClassView::redirect( $Module, 'changeclass/result/' . $result['restore_point'] );
            }
            $lines = array();
            foreach ( $result['errors'] as $error )
                $lines[] = \expChangeClassView::translate( $error );
            \expChangeClassView::flash( !empty( $result['stale'] ) ? 'warn' : 'bad',
                                        !empty( $result['stale'] ) ? \ezpI18n::tr( 'extension/expchangeclass', 'Nothing was changed: check the new preview.' )
                                                                    : \ezpI18n::tr( 'extension/expchangeclass', 'The class was not changed. The content is exactly as it was.' ),
                                        $lines );
            return \expChangeClassView::redirect( $Module, $result['restore_point'] !== '' ? 'changeclass/result/' . $result['restore_point'] : $here );
        }
        if ( $posted !== null )
        {
            \expChangeClassView::keepMapping( $key, $posted );
            return \expChangeClassView::redirect( $Module, $here );
        }

        $plan = \expChangeClassService::preview( $object, $classID, \expChangeClassView::keptMapping( $key ) );
        if ( !$plan['dest_class'] )
            return $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        $translated = \expChangeClassView::translatePlan( $plan );

        return \expChangeClassView::page( 'map_attributes', array(
            'node' => $node,
            'object' => $object,
            'plan' => $plan,
            'errors' => $translated['errors'],
            'warnings' => $translated['warnings'],
            'source_class' => $plan['source_class'],
            'dest_class' => $plan['dest_class'],
            'facts' => $plan['facts'],
            'command' => \expChangeClassView::command( $object->attribute( 'id' ), $plan['dest_class']->attribute( 'identifier' ), $plan['mapping'] ),
            'form_uri' => $here,
        ), \expChangeClassView::path( \ezpI18n::tr( 'extension/expchangeclass', 'Map the attributes' ), $node ) );
    }
}

}
