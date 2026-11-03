<?php
/**
 * The limitations of the changeclass policies and the editor policies, on plain arrays (no kernel, no database).
 *  AC-01 - matches(): Class, NewClass, Section, Node, Subtree and the assignment's User_Section and User_Subtree; an
 *          unknown limitation refuses; NewClass not known yet (the class choice) does not refuse
 *  AC-02 - allows(): yes, no, and limited (any policy that matches)
 *  PO-01 - plan(): convert and restore (bulk only when asked), each skipped when the role holds it in any form
 *  PO-02 - plan() twice after granting adds nothing (idempotent), and revokePlan() names only changeclass/convert,
 *          restore and bulk
 *  PO-03 - limitationsFor(): bulk takes Class and NewClass only, empty values are left out; describe()
 *  RP-01 - restore point ids: only the form create() makes is read (no path)
 *
 * Run: php vendor/bin/phpunit --bootstrap tests/bootstrap.php extension/expchangeclass/tests/phpunit/unit/
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

require_once dirname( __DIR__, 3 ) . '/classes/expchangeclassaccess.php';
require_once dirname( __DIR__, 3 ) . '/classes/expchangeclasspolicies.php';
require_once dirname( __DIR__, 3 ) . '/classes/expchangeclassrestorepoint.php';

class expChangeClassAccessTest extends PHPUnit\Framework\TestCase
{
    private static function subject( array $override = array() )
    {
        return $override + array( 'class_id' => 2, 'new_class_id' => 20, 'section_id' => 1, 'node_ids' => array( 61, 90 ),
                                  'path_strings' => array( '/1/2/60/61/', '/1/43/90/' ) );
    }

    public function testMatches()
    {
        $s = self::subject();
        $this->assertTrue( expChangeClassAccess::matches( array(), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'Class' => array( 2, 3 ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Class' => array( 3 ) ), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'NewClass' => array( '20' ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'NewClass' => array( '21' ) ), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'NewClass' => array( '21' ) ), self::subject( array( 'new_class_id' => null ) ) ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'Section' => array( 1 ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'User_Section' => array( 3 ) ), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'Node' => array( 90 ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Node' => array( 2 ) ), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'Subtree' => array( '/1/2/' ) ), $s ) );
        $this->assertTrue( expChangeClassAccess::matches( array( 'User_Subtree' => array( '/1/43/' ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Subtree' => array( '/1/5/' ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Subtree' => array( '/1/2/60/61/62/' ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Owner' => array( 1 ) ), $s ) );
        $this->assertFalse( expChangeClassAccess::matches( array( 'Class' => array( 2 ), 'Section' => array( 9 ) ), $s ) );
    }

    public function testAllows()
    {
        $s = self::subject();
        $this->assertTrue( expChangeClassAccess::allows( array( 'accessWord' => 'yes' ), $s ) );
        $this->assertFalse( expChangeClassAccess::allows( array( 'accessWord' => 'no' ), $s ) );
        $this->assertFalse( expChangeClassAccess::allows( array(), $s ) );
        $limited = array( 'accessWord' => 'limited', 'policies' => array( 'p1' => array( 'Class' => array( 5 ) ), 'p2' => array( 'Subtree' => array( '/1/43/' ) ) ) );
        $this->assertTrue( expChangeClassAccess::allows( $limited, $s ) );
        $this->assertFalse( expChangeClassAccess::allows( $limited, self::subject( array( 'path_strings' => array( '/1/2/' ) ) ) ) );
    }

    private static function functions( array $plan, $action )
    {
        $out = array();
        foreach ( $plan as $entry )
            if ( $entry['action'] === $action )
                $out[] = $entry['function'];
        return $out;
    }

    public function testPlan()
    {
        $this->assertSame( array( 'convert', 'restore' ), self::functions( expChangeClassPolicies::plan( array() ), 'grant' ) );
        $this->assertSame( array( 'convert', 'restore', 'bulk' ), self::functions( expChangeClassPolicies::plan( array(), array(), true ), 'grant' ) );
        $held = array( array( 'module' => 'changeclass', 'function' => 'convert', 'limitations' => array( 'Class' => array( '2' ) ) ) );
        $this->assertSame( array( 'restore' ), self::functions( expChangeClassPolicies::plan( $held ), 'grant' ) );
        $this->assertSame( array(), self::functions( expChangeClassPolicies::plan( array( array( 'module' => 'changeclass', 'function' => '*' ) ) ), 'grant' ) );
        $this->assertSame( array(), self::functions( expChangeClassPolicies::plan( array( array( 'module' => '*', 'function' => '*' ) ), array(), true ), 'grant' ) );
    }

    public function testIdempotentAndRevoke()
    {
        $policies = array( array( 'id' => 1, 'module' => 'content', 'function' => 'edit', 'limitations' => array() ) );
        $id = 10;
        foreach ( expChangeClassPolicies::plan( $policies ) as $entry )
            if ( $entry['action'] === 'grant' )
                $policies[] = array( 'id' => $id++, 'module' => $entry['module'], 'function' => $entry['function'], 'limitations' => $entry['limitations'] );
        $this->assertSame( array(), self::functions( expChangeClassPolicies::plan( $policies ), 'grant' ) );
        $policies[] = array( 'id' => 99, 'module' => 'changeclass', 'function' => 'administrate', 'limitations' => array() );
        $revoked = array_map( function ( $p ) { return $p['function']; }, expChangeClassPolicies::revokePlan( $policies ) );
        $this->assertSame( array( 'convert', 'restore' ), $revoked );
    }

    public function testLimitations()
    {
        $asked = array( 'Class' => array( '2' ), 'NewClass' => array( '20' ), 'Section' => array( '1', '' ), 'Subtree' => array(), 'Owner' => array( '1' ) );
        $this->assertSame( array( 'Class' => array( '2' ), 'NewClass' => array( '20' ), 'Section' => array( '1' ) ), expChangeClassPolicies::limitationsFor( 'convert', $asked ) );
        $this->assertSame( array( 'Class' => array( '2' ), 'NewClass' => array( '20' ) ), expChangeClassPolicies::limitationsFor( 'bulk', $asked ) );
        $plan = expChangeClassPolicies::plan( array(), $asked );
        $this->assertSame( array( 'Class' => array( '2' ), 'NewClass' => array( '20' ), 'Section' => array( '1' ) ), $plan[0]['limitations'] );
        $this->assertSame( 'Class: 2; NewClass: 20', expChangeClassPolicies::describe( array( 'Class' => array( '2' ), 'NewClass' => array( '20' ) ) ) );
        $this->assertStringContainsString( 'no limitations', expChangeClassPolicies::describe( array() ) );
    }

    public function testRestorePointIDs()
    {
        $this->assertTrue( expChangeClassRestorePoint::validID( '20261103-101500-57-a1b2c3' ) );
        foreach ( array( '../etc/passwd', '20261103-101500-57-a1b2c3.json', '20261103-101500-57', '', '20261103-101500-x-a1b2c3' ) as $bad )
            $this->assertFalse( expChangeClassRestorePoint::validID( $bad ), $bad );
        expChangeClassRestorePoint::$directory = '/nonexistent';
        $this->assertNull( expChangeClassRestorePoint::file( '../x' ) );
        $this->assertSame( '/nonexistent/20261103-101500-57-a1b2c3.json', expChangeClassRestorePoint::file( '20261103-101500-57-a1b2c3' ) );
        expChangeClassRestorePoint::$directory = null;
    }
}
