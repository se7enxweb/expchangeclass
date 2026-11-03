<?php
/**
 * changeclass/select_class/<NodeID>: step 1 of a class change. What the object is (class, versions, languages,
 * drafts, sub-items, relations) and the class it should become. A POST with SelectDestinationClassButton goes on to
 * changeclass/map_attributes/<NodeID>/<ClassID> (303).
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class SelectClass extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        $nodeID = isset( $Params['NodeID'] ) ? (int)$Params['NodeID'] : 0;
        if ( !$nodeID && $http->hasPostVariable( 'NodeID' ) )
            $nodeID = (int)$http->postVariable( 'NodeID' );
        $node = \eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node instanceof \eZContentObjectTreeNode || !$node->attribute( 'can_read' ) )
            return $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        $object = $node->attribute( 'object' );

        if ( $http->hasPostVariable( 'CancelButton' ) )
            return \expChangeClassView::redirect( $Module, $node->attribute( 'url_alias' ) );
        if ( $http->hasPostVariable( 'SelectDestinationClassButton' ) && $http->hasPostVariable( 'DestinationClassID' ) )
        {
            $classID = (int)$http->postVariable( 'DestinationClassID' );
            if ( !\expChangeClassAccess::can( 'convert', $object, $classID ) )
            {
                \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'Your role does not allow changing this object into that class (policy changeclass/convert).' ) );
                return \expChangeClassView::redirect( $Module, 'changeclass/select_class/' . $nodeID );
            }
            return \expChangeClassView::redirect( $Module, 'changeclass/map_attributes/' . $nodeID . '/' . $classID );
        }

        $sourceClass = \eZContentClass::fetch( (int)$object->attribute( 'contentclass_id' ) );
        $classes = array();
        $allowed = 0;
        foreach ( \expChangeClassView::classOptions( (int)$object->attribute( 'contentclass_id' ) ) as $option )
        {
            $option['allowed'] = \expChangeClassAccess::can( 'convert', $object, $option['id'] );
            if ( $option['allowed'] )
                $allowed++;
            $classes[] = $option;
        }
        $facts = $sourceClass ? \expChangeClassService::facts( $object, $sourceClass, $sourceClass ) : array();

        return \expChangeClassView::page( 'select_class', array(
            'node' => $node,
            'object' => $object,
            'source_class' => $sourceClass,
            'facts' => $facts,
            'classes' => $classes,
            'allowed_count' => $allowed,
            'can_convert' => \expChangeClassAccess::can( 'convert', $object ),
            'restore_points' => \expChangeClassRestorePoint::listRecent( 5, null, (int)$object->attribute( 'id' ) ),
        ), \expChangeClassView::path( \ezpI18n::tr( 'extension/expchangeclass', 'Choose the new class' ), $node ) );
    }
}

}
