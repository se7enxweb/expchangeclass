<?php
/**
 * changeclass/action: the entry of the context menus ("Change content class", a POST with NodeID and
 * SelectSourceObjectButton), sent on to changeclass/select_class/<NodeID> (303).
 *
 * The single page of the earlier versions posted the change itself here (ModifyObjectContentClassButton with
 * SourceObjectID, DestinationClassID and SourceAttribute[]). That still works and now goes through the same plan,
 * restore point and transaction as changeclass/map_attributes, then to changeclass/result/<id>; a plan that refuses
 * opens changeclass/map_attributes with the reason. "Copy object" (ChangeObjectContentClassButton) was never
 * available and is refused.
 *
 * Originally by Bartek Modzelewski (eZChangeclass, GNU General Public License v2.0).
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class Action extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        if ( $Module->isCurrentAction( 'SelectSourceObject' ) && $http->hasPostVariable( 'NodeID' ) )
            return \expChangeClassView::redirect( $Module, 'changeclass/select_class/' . (int)$http->postVariable( 'NodeID' ) );

        if ( $Module->isCurrentAction( 'ModifyObject' ) && $http->hasPostVariable( 'SourceObjectID' ) && $http->hasPostVariable( 'DestinationClassID' ) )
        {
            $object = \eZContentObject::fetch( (int)$http->postVariable( 'SourceObjectID' ) );
            if ( !$object instanceof \eZContentObject )
                return $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
            $classID = (int)$http->postVariable( 'DestinationClassID' );
            $nodeID = $http->hasPostVariable( 'SourceNodeID' ) ? (int)$http->postVariable( 'SourceNodeID' ) : (int)$object->attribute( 'main_node_id' );
            $mapping = \expChangeClassView::postedMapping();
            $result = \expChangeClassService::apply( $object, $classID, $mapping, array( 'via' => 'admin' ) );
            if ( $result['ok'] )
            {
                \expChangeClassView::flash( 'ok', \ezpI18n::tr( 'extension/expchangeclass', 'The class was changed.' ) );
                return \expChangeClassView::redirect( $Module, 'changeclass/result/' . $result['restore_point'] );
            }
            $lines = array();
            foreach ( $result['errors'] as $error )
                $lines[] = \expChangeClassView::translate( $error );
            \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'The class was not changed. The content is exactly as it was.' ), $lines );
            if ( $mapping !== null )
                \expChangeClassView::keepMapping( $nodeID . '/' . $classID, $mapping );
            return \expChangeClassView::redirect( $Module, 'changeclass/map_attributes/' . $nodeID . '/' . $classID );
        }

        if ( $Module->isCurrentAction( 'CreateObject' ) )
            \eZDebug::writeError( 'Copying an object into another class is not available', __METHOD__ );
        return \expChangeClassView::redirect( $Module, 'changeclass/dashboard' );
    }
}

}
