<?php
/**
 * The code of extension/expchangeclass/modules/changeclass/select_class.php, moved into a class (#207 stage 1). The file extension/expchangeclass/modules/changeclass/select_class.php is one call to it.
 * Guide: doc/bc/6.0/cli_cronjob_view_abstractions.md
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class SelectClass extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        // the including function's variables ($Params, $Module, $cli, ...)
        foreach ( array_keys( $scope ) as $__name )
            if ( $__name !== 'this' && $__name !== 'scope' )
                ${$__name} = &$scope[$__name];
        unset( $__name );

        include_once( 'kernel/common/template.php' );
        include_once( "lib/ezutils/classes/ezhttptool.php" );
        include_once( 'kernel/classes/ezcontentobject.php' );
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        $userID = $Params['Parameters'][0];

        $sourceNodeID = $Module->ViewParameters[0];
        $node = \eZContentObjectTreeNode::fetch( $sourceNodeID );
        if ( !$node instanceof \eZContentObjectTreeNode )
        {
            \eZDebug::writeError( "Node '$sourceNodeID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }
        $sourceObjectID = $node->attribute( 'contentobject_id' );

        $sourceObject = \eZContentObject::fetch( $sourceObjectID );
        if ( !$sourceObject instanceof \eZContentObject )
        {
            \eZDebug::writeError( "Source object '$sourceObjectID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }
        $sourceClassID = $sourceObject->attribute( 'contentclass_id' );


        $tpl = \eZTemplate::factory();
        $tpl->setVariable( 'source_class_id', $sourceClassID );
        $tpl->setVariable( 'source_object_id', $sourceObjectID );
        $tpl->setVariable( 'source_node_id', $sourceNodeID );

        $Result = array();
        $Result['content'] = $tpl->fetch( 'design:changeclass/select_class.tpl' );
        $Result['path'] = array( array( 'url' => false,
                                        'text' => 'Select destination class' ) );

        return $this->viewResult( isset( $Result ) ? $Result : null, null );
    }
}

}
