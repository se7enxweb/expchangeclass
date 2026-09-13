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

include_once( 'kernel/common/template.php' );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( 'kernel/classes/ezcontentobject.php' );
$Module = $Params['Module'];
$http = eZHTTPTool::instance();

$userID = $Params['Parameters'][0];

$sourceNodeID = $Module->ViewParameters[0];
$node = eZContentObjectTreeNode::fetch( $sourceNodeID );
if ( !$node instanceof eZContentObjectTreeNode )
{
    eZDebug::writeError( "Node '$sourceNodeID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
$sourceObjectID = $node->attribute( 'contentobject_id' );

$sourceObject = eZContentObject::fetch( $sourceObjectID );
if ( !$sourceObject instanceof eZContentObject )
{
    eZDebug::writeError( "Source object '$sourceObjectID' not found", __FILE__ );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
$sourceClassID = $sourceObject->attribute( 'contentclass_id' );


$tpl = eZTemplate::factory();
$tpl->setVariable( 'source_class_id', $sourceClassID );
$tpl->setVariable( 'source_object_id', $sourceObjectID );
$tpl->setVariable( 'source_node_id', $sourceNodeID );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:changeclass/select_class.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => 'Select destination class' ) );








?>
