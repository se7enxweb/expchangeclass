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

// Converting an object rewrites its attributes across every version and
// language, so the views are gated on a policy rather than on module access
// alone. Upstream declared no functions at all, which left the only possible
// grant an all-or-nothing one on the module.
$FunctionList = array();
$FunctionList['convert'] = array();

$ViewList = array();
$ViewList['select_class'] = array(
    'script' => 'select_class.php',
    'functions' => array( 'convert' ),
    'params' => array (  ) );
$ViewList['map_attributes'] = array(
    'script' => 'map_attributes.php',
    'functions' => array( 'convert' ),
    'params' => array (  ) );
$ViewList['action'] = array(
    'script' => 'action.php',
    'functions' => array( 'convert' ),
    'single_post_actions' => array( 'SelectSourceObjectButton' => 'SelectSourceObject',
                                    'SelectDestinationClassButton' => 'SelectDestinationClass',
                                    'ChangeObjectContentClassButton' => 'CreateObject',
                                    'ModifyObjectContentClassButton' => 'ModifyObject' ),
    'params' => array () );

?>
