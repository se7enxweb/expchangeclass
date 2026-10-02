<?php
/**
 * The code of extension/expchangeclass/modules/changeclass/map_attributes.php, moved into a class (#207 stage 1). The file extension/expchangeclass/modules/changeclass/map_attributes.php is one call to it.
 * Guide: doc/bc/6.0/cli_cronjob_view_abstractions.md
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class MapAttributes extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        // the including function's variables ($Params, $Module, $cli, ...)
        foreach ( array_keys( $scope ) as $__name )
            if ( $__name !== 'this' && $__name !== 'scope' )
                ${$__name} = &$scope[$__name];
        unset( $__name );

        require_once( $this->scriptDir() . '/../../classes/functions.php' );
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();

        $sourceObjectID     = $http->postVariable( 'SourceObjectID' );
        $sourceNodeID       = $http->postVariable( 'SourceNodeID' );
        $destinationClassID = $http->postVariable( 'DestinationClassID' );
        $ini = \eZINI::instance( 'changeclass.ini' );

        // Every one of these was dereferenced without a check, so a stale form, a
        // deleted object or a class removed between the two steps of the wizard was a
        // fatal rather than a message.
        $sourceObject = \eZContentObject::fetch( $sourceObjectID );
        if ( !$sourceObject instanceof \eZContentObject )
        {
            \eZDebug::writeError( "Source object '$sourceObjectID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }

        $sourceClassID = $sourceObject->attribute( 'contentclass_id' );
        $sourceClass = \eZContentClass::fetch( $sourceClassID );
        if ( !$sourceClass instanceof \eZContentClass )
        {
            \eZDebug::writeError( "Source class '$sourceClassID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }

        $destinationClass = \eZContentClass::fetch( $destinationClassID );
        if ( !$destinationClass instanceof \eZContentClass )
        {
            \eZDebug::writeError( "Destination class '$destinationClassID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }

        $warnings = array();

        // checking children
        $sourceNode = \eZContentObjectTreeNode::fetch( $sourceNodeID );
        if ( !$sourceNode instanceof \eZContentObjectTreeNode )
        {
            \eZDebug::writeError( "Source node '$sourceNodeID' not found", $this->scriptFile() );
            return $this->viewResult( isset( $Result ) ? $Result : null,  $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' ) );
        }
        $sourceChildrenCount = $sourceNode->attribute( 'children_count' );
        if ( $sourceClass->attribute( 'is_container' ) == 1
        		&& $destinationClass->attribute( 'is_container' ) == 0 )
        {
            $warnings['no_children'] = true;
        }
        // checking lost attributes
        foreach ( $destinationClass->dataMap() as $attr )
        {
            $destinationDataTypeArray[] = $attr->DataTypeString;
        }
        $lost_attributes = array();
        $i = 0;

        $additionalAttributeMap = array();
        foreach ( $sourceClass->dataMap() as $sourceClassAttr )
        {

            if ( $ini->hasGroup( $sourceClassAttr->DataTypeString ) )
            {
                //In case it's a custom convertion script for this datatype, we need to search for that
                $possible_dest = $ini->variable( $sourceClassAttr->DataTypeString, 'SupportedDestination' );
                if ( !is_array( $possible_dest ) ) $possible_dest = array( $possible_dest );
                $additionalAttributeMap[$sourceClassAttr->DataTypeString] = $possible_dest;
                foreach( $possible_dest as $p_dest )
                {
                    if ( in_array( $p_dest, $destinationDataTypeArray ) )
                    {
                        continue 2;
                    }
                }
            }
            if ( !in_array( $sourceClassAttr->DataTypeString, $destinationDataTypeArray ) )
            {
                // Don't give warning if it's possible to make simple conversion
                $simpleConversion = \conversionFunctions::getSimpleConversionArray();
                foreach ( $destinationDataTypeArray as $destinationDataType )
                {
                    if ( isset( $simpleConversion[$sourceClassAttr->DataTypeString] ) && in_array( $destinationDataType, $simpleConversion[$sourceClassAttr->DataTypeString] ) )
                    {
                        continue 2;
                    }
                }
                if ( isset( $sourceClassAttr->Name)) $lost_attributes[$i]['name'] = $sourceClassAttr->Name;
                else $lost_attributes[$i]['name'] = $sourceClassAttr->Identifier;
                $lost_attributes[$i]['datatype'] = $sourceClassAttr->DataTypeString;
                $i++;
            }
        }
        if ( $i > 0 )
        {
            $warnings['lost_attributes'] = $lost_attributes;
        }

        // checking for unsupported datatypes
        $unsupported = $ini->variable( 'General', 'UnsupportedDataTypeArray' );
        $unsupportedDataTypes = array();
        foreach ( $unsupported as $datatype )
        {
            if ( in_array( $datatype, $destinationDataTypeArray ) )
                $unsupportedDataTypes[] = $datatype;
        }
        if ( !empty( $unsupportedDataTypes ) )
        {
            $warnings['unsupported_datatypes'] = $unsupportedDataTypes;
        }

        //echo "<pre>";
        //print_r( $unsupportedDataTypes );
        //print_r(  $sourceDataTypeArray );
        //echo "</pre>";



        $tpl = \eZTemplate::factory();
        $tpl->setVariable( 'source_class_id', $sourceClassID );
        $tpl->setVariable( 'source_object_id', $sourceObjectID );
        $tpl->setVariable( 'source_node_id', $sourceNodeID );
        $tpl->setVariable( 'destination_class_id', $destinationClassID );
        $tpl->setVariable( 'additional_attribute_map', $additionalAttributeMap );
        $tpl->setVariable( 'warnings', $warnings );

        $Result = array();
        $Result['content'] = $tpl->fetch( 'design:changeclass/map_attributes.tpl' );
        $Result['path'] = array( array( 'url' => false,
                                        'text' => 'Attributes mapping' ) );

        return $this->viewResult( isset( $Result ) ? $Result : null, null );
    }
}

}
