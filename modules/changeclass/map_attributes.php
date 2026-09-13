<?php
//
// Created on: <13-Jan-2007>
//
// SOFTWARE NAME: expChangeClass
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2007-2013 Bartek Modzelewski
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//


// The conversion engine is loaded explicitly. Upstream left this commented out
// and relied on the class turning up in the generated extension autoload array,
// so on any installation where that had not been regenerated the module died
// with "Class conversionFunctions not found". __DIR__ keeps it correct whatever
// the working directory is.
require_once( __DIR__ . '/../../classes/functions.php' );
$Module = $Params['Module'];
$http = eZHTTPTool::instance();

$sourceObjectID     = $http->postVariable( 'SourceObjectID' );
$sourceNodeID       = $http->postVariable( 'SourceNodeID' );
$destinationClassID = $http->postVariable( 'DestinationClassID' );
$ini = eZINI::instance( 'changeclass.ini' );

// Every one of these was dereferenced without a check, so a stale form, a
// deleted object or a class removed between the two steps of the wizard was a
// fatal rather than a message.
$sourceObject = eZContentObject::fetch( $sourceObjectID );
if ( !$sourceObject instanceof eZContentObject )
{
    eZDebug::writeError( "Source object '$sourceObjectID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$sourceClassID = $sourceObject->attribute( 'contentclass_id' );
$sourceClass = eZContentClass::fetch( $sourceClassID );
if ( !$sourceClass instanceof eZContentClass )
{
    eZDebug::writeError( "Source class '$sourceClassID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$destinationClass = eZContentClass::fetch( $destinationClassID );
if ( !$destinationClass instanceof eZContentClass )
{
    eZDebug::writeError( "Destination class '$destinationClassID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$warnings = array();

// checking children
$sourceNode = eZContentObjectTreeNode::fetch( $sourceNodeID );
if ( !$sourceNode instanceof eZContentObjectTreeNode )
{
    eZDebug::writeError( "Source node '$sourceNodeID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
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
        $simpleConversion = conversionFunctions::getSimpleConversionArray();
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



$tpl = eZTemplate::factory();
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






?>