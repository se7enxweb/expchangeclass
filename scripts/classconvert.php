<?php
//
// Created on: <15-Jun-2007 ar@ez>
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


if( !file_exists( 'extension/expchangeclass/scripts' ) || !is_dir( 'extension/expchangeclass/scripts' ) )
{
    echo "Please run this script from the root document directory!\n";
    echo 'Current working directory is: ' . getcwd() . "\n";
    exit;
}

include_once( 'autoload.php' );

$cli = eZCLI::instance();

$script = eZScript::instance( array( 'description' => ( "\nThis script performs batch conversion of objects of a specific class!\n" .
                                                         "\nBefore running this you should use the gui part of this extension, witch will create a conversion file if you select:\n 'Generate parameters for converting all instances of this class'.\n"  ),
                                      'use-session' => false,
                                      'use-modules' => true,
                                      'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[db-host:][db-user:][db-password:][db-database:][db-type:][param-file:][sub-tree:]",
                                "",
                                array( 'db-host' => "Database host",
                                       'db-user' => "Database user",
                                       'db-password' => "Database password",
                                       'db-database' => "Database name",
                                       'db-type' => "Database type, e.g. mysql or postgresql",
                                       'param-file' => "Parameter filename",
                                       'sub-tree' => "Restrict by Subtree Node Id"
                                       ) );
$script->initialize();

$dbUser = $options['db-user'];
$dbPassword = $options['db-password'];
$dbHost = $options['db-host'];
$dbName = $options['db-database'];
$dbImpl = $options['db-type'];
$paramFile = $options['param-file'];
$subTree = (int) $options['sub-tree'];
$isQuiet = $script->isQuiet();

if ( $dbHost or $dbName or $dbUser or $dbImpl )
{
    $params = array( 'use_defaults' => false );
    if ( $dbHost !== false )
    {
        $params['server'] = $dbHost;
    }

    if ( $dbUser !== false )
    {
        $params['user'] = $dbUser;
        $params['password'] = '';
    }

    if ( $dbPassword !== false )
    {
        $params['password'] = $dbPassword;
    }

    if ( $dbName !== false )
    {
        $params['database'] = $dbName;
    }

    $db = eZDB::instance( $dbImpl, $params, true );
    eZDB::setInstance( $db );
}
else
{
    $db = eZDB::instance();
}

if ( !$db->isConnected() )
{
    $cli->notice( "Can't initialize database connection.\n" );
    $script->shutdown( 1 );
}

//$paramFile
$file_path = eZSys::cacheDirectory();
$file_name = $file_path . '/' . $paramFile;

if( !file_exists( $file_name ) )
{
    $cli->notice( "File $paramFile not found!" );
    $script->shutdown( 1 );
}


$handle       = fopen( $file_name, "r" );
$line         = 1;
$class_array  = 0;
$mapping      = array();

//Expected file format in $paramFile
//source_class_identifier:dest_class_identifier
//source_attribute_identifier_1:dest_attribute_identifier_1
//source_attribute_identifier_2:dest_attribute_identifier_2
//and so on

if ( $handle )
{
    while ( !feof( $handle ) )
    {
        $buffer = trim( fgets( $handle, 1024 ) );
        // Blank and malformed lines used to be indexed blindly: a trailing
        // newline produced $temp[1] on a one-element array and registered an
        // empty destination identifier in the mapping.
        if ( $buffer === '' )
            continue;

        $temp = explode( ':', $buffer );
        if ( count( $temp ) < 2 )
        {
            $cli->notice( "Skipping malformed line in $paramFile: '$buffer'" );
            continue;
        }

        if ( $line === 1 )
            $class_array = $temp;
        else
            $mapping[trim( $temp[1] )] = trim( $temp[0] );

        $line++;
    }
    fclose($handle);
}
else
{
    $cli->notice( "File $paramFile could not be opened!");
    $script->shutdown( 1 );
}


if ( !$class_array || count( $class_array ) < 2 ||
     trim( $class_array[0] ) === '' || trim( $class_array[1] ) === '' || !$mapping )
{
    $cli->notice( "Didn't find class identifiers or no attributes where found from $paramFile!" );
    $script->shutdown( 1 );
}

$class_array[0] = trim( $class_array[0] );
$class_array[1] = trim( $class_array[1] );

if ( $class_array[0] === $class_array[1] )
{
    $cli->notice( "Source and destination class are both '{$class_array[0]}'; nothing to convert." );
    $script->shutdown( 1 );
}

if (!$subTree) $subTree = 1;

//start feching objects of class: $class_array[0]
$offset = 0;
$limit = 100;
$line = 0;
$failedTotal = 0;
$debug = array();

$nodeCount = eZContentObjectTreeNode::subTreeCountByNodeID( array( 'ClassFilterType' => 'include',
                                                           'ClassFilterArray' => array( $class_array[0] ),
                                                           'Limitation' => array(),
                                                           'MainNodeOnly' => true ),
                                                    $subTree );

if ( !$isQuiet )
{
    $cli->notice( 'Number of objects found: ' .$nodeCount );
}

do
{
	$nodeArray = eZFunctionHandler::execute( 'content', 'list', array(
																'parent_node_id' => $subTree,
																'depth' => 99,
																'limitation' => array(),
																'offset' => $offset,
																'limit' => $limit,
																'ignore_visibility' => true,
																'class_filter_type' => 'include',
																'class_filter_array' => array( $class_array[0] )
															));

    if ( !$nodeArray ) {
	    break;
    }

    $failedInBatch = 0;

    foreach ( $nodeArray as $node )
    {
        $conversionError = null;
        $temp = conversionFunctions::convertObject( $node->attribute('contentobject_id'), $class_array[1], $mapping, $conversionError );

        if ( !$temp )
        {
            $temp_string = 'Error: ObjectId ' . $node->attribute('contentobject_id') . ' with class ' . $class_array[1] . ' was not converted: ' . (string) $conversionError;
            $cli->notice( $temp_string);
            $debug[] = $temp_string . "\n";
            ++$failedInBatch;
        }
        else
        {
            $line++;
	        $cli->output( "+", false );
            $debug[] = $node->attribute('name') . ',' . $node->attribute('node_id') . ',' . $node->attribute('contentobject_id') . "\n";
        }
    }
    if ( !$isQuiet )
    {
        $cli->notice( $line . ' objects converted, ' . ($nodeCount - $line - $failedTotal) . ' left.' );
    }

    // Free the object caches every pass. This used to be inside the
    // !$isQuiet branch, so a quiet run held every object it had touched.
    clearCache();

    // A converted object no longer matches the class filter and drops out of
    // the next page, so the window stays at the front of the list. Objects that
    // failed do not drop out, so the offset has to step over them - without
    // this the same failing page was fetched forever.
    $failedTotal += $failedInBatch;
    $offset = $failedTotal;

    if ( $failedInBatch === count( $nodeArray ) )
    {
        $cli->notice( 'No object in the last batch could be converted; stopping.' );
        break;
    }

} while ( count( $nodeArray ) );


$ini = eZINI::instance( 'changeclass.ini' );
if ( $ini->variable( 'General', 'ScriptLog' ) == 'enabled' )
{
    $file_path = eZSys::cacheDirectory();
    $fp = fopen($file_path.'/logExpChangeClass.txt', "a+");
    if ( $fp )
    {
        foreach( $debug as $debug_line)
        {
            fputs($fp, $debug_line);
        } 
        fclose($fp);
    }
    else
    {
        $cli->notice( "Could not open log file for writing! \n"  . $file_path.'/logExpChangeClass.txt' );
        $script->shutdown(1);
    }
}

if ( !$isQuiet )
{
    $cli->notice( "Done. $line objects converted!" );
	//$cli->notice( "Max memory usage:" . memory_get_peak_usage() );

}

$script->shutdown();



function clearCache()
{
	eZContentObject::clearCache();
	unset( $GLOBALS['eZContentObjectContentObjectCache'] );
	unset( $GLOBALS['eZContentObjectDataMapCache'] );
	unset( $GLOBALS['eZContentObjectVersionCache'] );
	unset( $GLOBALS['eZContentClassAttributeCache'] );
}