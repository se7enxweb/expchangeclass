<?php
//
// SOFTWARE NAME: expChangeClass
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2007 Bartek Modzelewski
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

$Module = array( 'name' => 'changeclass' );

// The policies and their limitations (classes/expchangeclassaccess.php). Converting an object rewrites its
// attributes across every version and language, so every view is gated on a policy rather than on module access
// alone, and a change also needs content/edit on the object.
$ClassLimitation = array(
    'name' => 'Class',
    'values' => array(),
    'class' => 'eZContentClass',
    'function' => 'fetchList',
    'parameter' => array( 0, false, false, array( 'name' => 'asc' ) ) );
$NewClassLimitation = array(
    'name' => 'NewClass',
    'values' => array(),
    'class' => 'eZContentClass',
    'function' => 'fetchList',
    'parameter' => array( 0, false, false, array( 'name' => 'asc' ) ) );
$SectionLimitation = array(
    'name' => 'Section',
    'values' => array(),
    'class' => 'eZSection',
    'function' => 'fetchList',
    'parameter' => array( false ) );
$NodeLimitation = array( 'name' => 'Node', 'values' => array() );
$SubtreeLimitation = array( 'name' => 'Subtree', 'values' => array() );

$FunctionList = array();
// one object: Class is its class now, NewClass the class it becomes
$FunctionList['convert'] = array( 'Class' => $ClassLimitation, 'NewClass' => $NewClassLimitation, 'Section' => $SectionLimitation,
                                  'Node' => $NodeLimitation, 'Subtree' => $SubtreeLimitation );
// undoing a change from its restore point (NewClass: the class the object gets back)
$FunctionList['restore'] = array( 'Class' => $ClassLimitation, 'NewClass' => $NewClassLimitation, 'Section' => $SectionLimitation,
                                  'Node' => $NodeLimitation, 'Subtree' => $SubtreeLimitation );
// every object of a class in a subtree, as a background job (each object is still checked against convert)
$FunctionList['bulk'] = array( 'Class' => $ClassLimitation, 'NewClass' => $NewClassLimitation );
// every user's changes, discarding restore points, the editor policies button
$FunctionList['administrate'] = array();

$ViewList = array();
$ViewList['select_class'] = array(
    'script' => 'select_class.php',
    'functions' => array( 'convert' ),
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'NodeID' ) );
$ViewList['map_attributes'] = array(
    'script' => 'map_attributes.php',
    'functions' => array( 'convert' ),
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'NodeID', 'ClassID' ) );
$ViewList['result'] = array(
    'script' => 'result.php',
    'functions' => array( 'convert' ),
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'RestorePointID' ) );
$ViewList['dashboard'] = array(
    'script' => 'dashboard.php',
    'functions' => array( 'convert' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'params' => array(),
    'unordered_params' => array( 'user' => 'User', 'state' => 'State' ) );
$ViewList['bulk'] = array(
    'script' => 'bulk.php',
    'functions' => array( 'bulk' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'params' => array( 'ClassID', 'NewClassID', 'NodeID' ) );
// the entry of the context menus (a POST with NodeID), and the old single page of the extension
$ViewList['action'] = array(
    'script' => 'action.php',
    'functions' => array( 'convert' ),
    'single_post_actions' => array( 'SelectSourceObjectButton' => 'SelectSourceObject',
                                    'SelectDestinationClassButton' => 'SelectDestinationClass',
                                    'ChangeObjectContentClassButton' => 'CreateObject',
                                    'ModifyObjectContentClassButton' => 'ModifyObject' ),
    'params' => array () );

?>
