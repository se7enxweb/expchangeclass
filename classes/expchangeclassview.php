<?php
/**
 * File containing the expChangeClassView class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * What the views of the changeclass module share: the redirect after a POST (303 to an address with view parameters,
 * never a query string), the message shown once after it, the mapping kept between the POST and the page it redirects
 * to, the breadcrumbs, and the page result.
 */
class expChangeClassView
{
    const MESSAGE = 'ExpChangeClassMessage';
    const MAPPING = 'ExpChangeClassMapping';

    /**
     * Redirects after a POST (303 See Other).
     *
     * @param eZModule $module
     * @param string $uri
     * @return mixed what the view returns
     */
    public static function redirect( $module, $uri )
    {
        $module->setRedirectStatus( '303 See Other' );
        return $module->redirectTo( $uri );
    }

    /**
     * Keeps a message for the next page.
     *
     * @param string $kind ok, warn, bad, info
     * @param string $text already translated
     * @param string[] $lines more lines (translated)
     */
    public static function flash( $kind, $text, array $lines = array() )
    {
        eZHTTPTool::instance()->setSessionVariable( self::MESSAGE, array( 'kind' => $kind, 'text' => $text, 'lines' => $lines ) );
    }

    /**
     * The message kept for this page, once.
     *
     * @return array|false
     */
    public static function takeMessage()
    {
        $http = eZHTTPTool::instance();
        if ( !$http->hasSessionVariable( self::MESSAGE ) )
            return false;
        $message = $http->sessionVariable( self::MESSAGE );
        $http->removeSessionVariable( self::MESSAGE );
        return is_array( $message ) ? $message : false;
    }

    /**
     * Keeps a mapping (and whether it was previewed) for the page of an object and class.
     */
    public static function keepMapping( $key, array $mapping )
    {
        $http = eZHTTPTool::instance();
        $all = $http->hasSessionVariable( self::MAPPING ) ? (array)$http->sessionVariable( self::MAPPING ) : array();
        $all[$key] = $mapping;
        // a handful is enough; the oldest go
        if ( count( $all ) > 10 )
            $all = array_slice( $all, -10, null, true );
        $http->setSessionVariable( self::MAPPING, $all );
    }

    /**
     * The mapping kept for a page, or null.
     */
    public static function keptMapping( $key )
    {
        $http = eZHTTPTool::instance();
        $all = $http->hasSessionVariable( self::MAPPING ) ? (array)$http->sessionVariable( self::MAPPING ) : array();
        return isset( $all[$key] ) && is_array( $all[$key] ) ? $all[$key] : null;
    }

    /**
     * The posted mapping: destination identifier => source identifier.
     *
     * @return array|null
     */
    public static function postedMapping()
    {
        $http = eZHTTPTool::instance();
        if ( !$http->hasPostVariable( 'SourceAttribute' ) || !is_array( $http->postVariable( 'SourceAttribute' ) ) )
            return null;
        $mapping = array();
        foreach ( $http->postVariable( 'SourceAttribute' ) as $to => $from )
        {
            if ( preg_match( '/^[A-Za-z0-9_]+$/', (string)$to ) && ( $from === '' || preg_match( '/^[A-Za-z0-9_]+$/', (string)$from ) ) )
                $mapping[(string)$to] = (string)$from;
        }
        return $mapping;
    }

    /**
     * A message of the planner translated.
     *
     * @param array $message code, text, params
     * @return string
     */
    public static function translate( array $message )
    {
        return ezpI18n::tr( 'extension/expchangeclass', $message['text'], null, array_map( 'strval', $message['params'] ) );
    }

    /**
     * The messages of a plan translated (errors, warnings) for the templates.
     *
     * @param array $plan
     * @return array errors, warnings: each code, text
     */
    public static function translatePlan( array $plan )
    {
        $out = array( 'errors' => array(), 'warnings' => array() );
        foreach ( array( 'errors', 'warnings' ) as $key )
            foreach ( isset( $plan[$key] ) ? $plan[$key] : array() as $message )
                $out[$key][] = array( 'code' => $message['code'], 'text' => self::translate( $message ) );
        return $out;
    }

    /**
     * The breadcrumbs: the change class overview, then the node, then the step.
     *
     * @param string $step translated
     * @param eZContentObjectTreeNode|null $node
     * @return array
     */
    public static function path( $step, $node = null )
    {
        $path = array( array( 'text' => ezpI18n::tr( 'extension/expchangeclass', 'Change class' ), 'url' => 'changeclass/dashboard' ) );
        if ( $node instanceof eZContentObjectTreeNode )
            $path[] = array( 'text' => (string)$node->attribute( 'name' ), 'url' => (string)$node->attribute( 'url_alias' ) );
        $path[] = array( 'text' => $step, 'url' => false );
        return $path;
    }

    /**
     * The result of a view.
     *
     * @param string $template design:changeclass/<name>.tpl
     * @param array $variables
     * @param array $path
     * @return array
     */
    public static function page( $template, array $variables, array $path )
    {
        $tpl = eZTemplate::factory();
        foreach ( $variables as $name => $value )
            $tpl->setVariable( $name, $value );
        $tpl->setVariable( 'message', self::takeMessage() );
        return array( 'content' => $tpl->fetch( 'design:changeclass/' . $template . '.tpl' ), 'path' => $path );
    }

    /**
     * The classes the destination select offers: every class but the object's, sorted by name, with its group.
     *
     * @param int $exceptID
     * @return array[] each: id, identifier, name, group
     */
    public static function classOptions( $exceptID = 0 )
    {
        $options = array();
        foreach ( (array)eZContentClass::fetchList( eZContentClass::VERSION_STATUS_DEFINED, true, false, array( 'name' => 'asc' ) ) as $class )
        {
            if ( (int)$class->attribute( 'id' ) === (int)$exceptID )
                continue;
            $name = trim( (string)$class->attribute( 'name' ) );
            $groups = array();
            foreach ( (array)$class->fetchGroupList() as $group )
                $groups[] = (string)$group->attribute( 'group_name' );
            $options[] = array( 'id' => (int)$class->attribute( 'id' ), 'identifier' => (string)$class->attribute( 'identifier' ),
                                'name' => $name !== '' ? $name : (string)$class->attribute( 'identifier' ),
                                'group' => $groups ? $groups[0] : '' );
        }
        usort( $options, function ( $a, $b ) { return strcasecmp( $a['group'] . "\0" . $a['name'], $b['group'] . "\0" . $b['name'] ); } );
        return $options;
    }

    /**
     * The command that does the same from a shell.
     */
    public static function command( $objectID, $classIdentifier, array $mapping )
    {
        $parts = array( './console exp:changeclass', '--object=' . (int)$objectID, '--class=' . $classIdentifier );
        foreach ( $mapping as $to => $from )
            $parts[] = '--map=' . $to . '=' . $from;
        return implode( ' ', $parts );
    }
}
