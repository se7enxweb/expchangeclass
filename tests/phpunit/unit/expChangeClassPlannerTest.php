<?php
/**
 * The plan of a class change, worked out on plain arrays (no kernel, no database).
 *  PL-01 - conversionKind(): same, simple ([SimpleConversion]), converter (a converter group), or null
 *  PL-02 - proposeMapping(): same identifier first, then the same datatype, then anything that converts; each source once
 *  PL-03 - plan(): a complete mapping is carried, converted, copied or left empty; the rows say how
 *  PL-04 - plan(): what refuses (same class, trash, user account, locked, no edit, job, unknown or incompatible
 *          attribute, unsupported datatype) and nothing is ok then
 *  PL-05 - plan(): what an editor should know (dropped content per language, required left empty, copies, drafts of
 *          others, no container, reverse relations)
 *  PL-06 - token(): changes with the object's version, modification, the classes and the mapping, not with its order
 *  PL-07 - render() fills the placeholders
 *
 * Run: php vendor/bin/phpunit --bootstrap tests/bootstrap.php extension/expchangeclass/tests/phpunit/unit/
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

require_once dirname( __DIR__, 3 ) . '/classes/expchangeclassplanner.php';

class expChangeClassPlannerTest extends PHPUnit\Framework\TestCase
{
    private static function rules()
    {
        return array(
            'simple' => array( 'eztext' => array( 'ezstring' ), 'ezstring' => array( 'eztext' ) ),
            'converters' => array( 'ezstring' => array( 'destinations' => array( 'ezxmltext', 'ezinteger' ), 'callable' => 'expChangeClassConverters::convertString' ) ),
            'unsupported' => array( 'ezuser' ),
        );
    }

    private static function attribute( $identifier, $datatype, $required = false )
    {
        return array( 'id' => crc32( $identifier . $datatype ) % 10000, 'identifier' => $identifier, 'name' => ucfirst( $identifier ),
                      'datatype' => $datatype, 'required' => $required, 'translatable' => true );
    }

    private static function facts( array $override = array() )
    {
        $facts = array(
            'source_class' => array( 'id' => 2, 'identifier' => 'article', 'name' => 'Article', 'is_container' => true,
                                     'attributes' => array( self::attribute( 'title', 'ezstring' ), self::attribute( 'intro', 'ezxmltext' ),
                                                            self::attribute( 'body', 'ezxmltext' ), self::attribute( 'caption', 'eztext' ),
                                                            self::attribute( 'image', 'ezimage' ) ) ),
            'dest_class' => array( 'id' => 20, 'identifier' => 'blog_post', 'name' => 'Blog post', 'is_container' => false,
                                   'attributes' => array( self::attribute( 'title', 'ezstring', true ), self::attribute( 'body', 'ezxmltext' ),
                                                          self::attribute( 'summary', 'ezstring' ), self::attribute( 'tags', 'ezkeyword', true ) ) ),
            'object' => array( 'id' => 57, 'name' => 'Hello', 'status' => 1, 'is_user' => false, 'locked' => false, 'children_count' => 0,
                               'languages' => array( 'eng-GB', 'ger-DE' ), 'current_user_id' => 14,
                               'versions' => array( array( 'version' => 1, 'status' => 3, 'creator_id' => 14 ), array( 'version' => 2, 'status' => 1, 'creator_id' => 14 ) ) ),
            'values' => array(
                'title' => array( 'eng-GB' => array( 'has_content' => true, 'text' => 'Hello' ), 'ger-DE' => array( 'has_content' => true, 'text' => 'Hallo' ) ),
                'image' => array( 'eng-GB' => array( 'has_content' => true, 'text' => 'hello.jpg' ), 'ger-DE' => array( 'has_content' => false, 'text' => '' ) ),
                'intro' => array( 'eng-GB' => array( 'has_content' => false, 'text' => '' ), 'ger-DE' => array( 'has_content' => false, 'text' => '' ) ),
            ),
            'reverse_relations' => 0, 'job' => '', 'can_edit' => true,
        );
        return array_replace_recursive( $facts, $override );
    }

    private static function codes( array $messages )
    {
        return array_map( function ( $m ) { return $m['code']; }, $messages );
    }

    public function testConversionKind()
    {
        $rules = self::rules();
        $this->assertSame( 'same', expChangeClassPlanner::conversionKind( 'ezstring', 'ezstring', $rules ) );
        $this->assertSame( 'simple', expChangeClassPlanner::conversionKind( 'eztext', 'ezstring', $rules ) );
        $this->assertSame( 'converter', expChangeClassPlanner::conversionKind( 'ezstring', 'ezxmltext', $rules ) );
        $this->assertNull( expChangeClassPlanner::conversionKind( 'ezimage', 'ezstring', $rules ) );
        $this->assertNull( expChangeClassPlanner::conversionKind( 'ezxmltext', 'ezstring', $rules ) );
    }

    public function testProposeMapping()
    {
        $facts = self::facts();
        $mapping = expChangeClassPlanner::proposeMapping( $facts['source_class']['attributes'], $facts['dest_class']['attributes'], self::rules() );
        $this->assertSame( array( 'title' => 'title', 'body' => 'body', 'summary' => 'caption', 'tags' => '' ), $mapping );
    }

    public function testPlanRows()
    {
        $plan = expChangeClassPlanner::plan( self::facts(), array( 'title' => 'title', 'body' => 'body', 'summary' => 'title', 'tags' => '' ), self::rules() );
        $this->assertTrue( $plan['ok'], json_encode( $plan['errors'] ) );
        $kinds = array();
        foreach ( $plan['rows'] as $row )
            $kinds[$row['identifier']] = $row['kind'];
        $this->assertSame( array( 'title' => 'same', 'body' => 'same', 'summary' => 'copy', 'tags' => 'empty' ), $kinds );
        $this->assertSame( 'Hallo', $plan['rows'][0]['values']['ger-DE']['text'] );
        $this->assertSame( array( 'intro', 'caption', 'image' ), array_map( function ( $d ) { return $d['identifier']; }, $plan['dropped'] ) );
        $this->assertSame( 2, $plan['stats']['versions'] );
        $this->assertSame( 16, $plan['stats']['rows_changed'] );
    }

    public function testConverterRowNamesItsConverter()
    {
        $facts = self::facts( array( 'dest_class' => array( 'attributes' => array( self::attribute( 'title', 'ezxmltext' ) ) ) ) );
        $facts['dest_class']['attributes'] = array( self::attribute( 'title', 'ezxmltext' ) );
        $plan = expChangeClassPlanner::plan( $facts, array( 'title' => 'title' ), self::rules() );
        $this->assertSame( 'converter', $plan['rows'][0]['kind'] );
        $this->assertSame( 'expChangeClassConverters::convertString', $plan['rows'][0]['converter'] );
    }

    public function testRefusals()
    {
        $rules = self::rules();
        $cases = array(
            'same_class' => self::facts( array( 'dest_class' => array( 'id' => 2 ) ) ),
            'in_trash' => self::facts( array( 'object' => array( 'status' => 2 ) ) ),
            'user_object' => self::facts( array( 'object' => array( 'is_user' => true ) ) ),
            'locked' => self::facts( array( 'object' => array( 'locked' => true ) ) ),
            'no_edit' => self::facts( array( 'can_edit' => false ) ),
            'job_running' => self::facts( array( 'job' => 'Remove the subtree of node 2' ) ),
        );
        foreach ( $cases as $code => $facts )
        {
            $plan = expChangeClassPlanner::plan( $facts, array(), $rules );
            $this->assertFalse( $plan['ok'], $code );
            $this->assertContains( $code, self::codes( $plan['errors'] ), $code );
        }
        $plan = expChangeClassPlanner::plan( self::facts(), array( 'title' => 'image', 'nope' => 'title', 'body' => 'missing' ), $rules );
        $this->assertFalse( $plan['ok'] );
        $codes = self::codes( $plan['errors'] );
        $this->assertContains( 'incompatible', $codes );
        $this->assertContains( 'unknown_destination', $codes );
        $this->assertContains( 'unknown_source', $codes );

        $facts = self::facts();
        $facts['dest_class']['attributes'][] = self::attribute( 'account', 'ezuser' );
        $this->assertContains( 'unsupported', self::codes( expChangeClassPlanner::plan( $facts, array(), $rules )['errors'] ) );
    }

    public function testWarnings()
    {
        $facts = self::facts( array( 'object' => array( 'children_count' => 3 ), 'reverse_relations' => 2 ) );
        $facts['object']['versions'][] = array( 'version' => 3, 'status' => 0, 'creator_id' => 99 );
        $plan = expChangeClassPlanner::plan( $facts, array( 'title' => 'title', 'body' => 'body', 'summary' => 'title', 'tags' => '' ), self::rules() );
        $codes = self::codes( $plan['warnings'] );
        foreach ( array( 'dropped_data', 'required_empty', 'copied', 'drafts_of_others', 'not_container', 'reverse_relations' ) as $code )
            $this->assertContains( $code, $codes, $code );
        // the image holds content in eng-GB only; intro holds none and is dropped silently
        $dropped = array();
        foreach ( $plan['warnings'] as $warning )
            if ( $warning['code'] === 'dropped_data' )
                $dropped[] = $warning['params']['%source'] . ':' . $warning['params']['%languages'];
        $this->assertSame( array( 'Image:eng-GB' ), $dropped );
        $this->assertTrue( $plan['ok'] );
    }

    public function testToken()
    {
        $parts = array( 'object_id' => 57, 'modified' => 100, 'current_version' => 2, 'source_class_id' => 2, 'dest_class_id' => 20,
                        'mapping' => array( 'title' => 'title', 'body' => 'body' ) );
        $token = expChangeClassPlanner::token( $parts );
        $this->assertSame( $token, expChangeClassPlanner::token( array( 'mapping' => array( 'body' => 'body', 'title' => 'title' ) ) + $parts ) );
        foreach ( array( 'modified' => 101, 'current_version' => 3, 'dest_class_id' => 21, 'mapping' => array( 'title' => '', 'body' => 'body' ) ) as $key => $value )
        {
            $changed = $parts;
            $changed[$key] = $value;
            $this->assertNotSame( $token, expChangeClassPlanner::token( $changed ), $key );
        }
    }

    public function testRender()
    {
        $message = expChangeClassPlanner::message( 'x', '%a into %b', array( '%a' => 'Article', '%b' => 5 ) );
        $this->assertSame( 'Article into 5', expChangeClassPlanner::render( $message ) );
    }
}
