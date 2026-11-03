<?php
/**
 * File containing the expChangeClassPolicies class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The policies that let editors change classes, given to a role (the Editor role by default) by the command
 * exp:addchangeclasspolicies (ext:expchangeclass:addpolicies) and by the button "Allow editors to change classes" of
 * changeclass/dashboard, and taken away again by --revoke and "Remove".
 *
 * Granted:
 *   changeclass/convert   change the class of one object (the object's own content/edit is still needed)
 *   changeclass/restore   undo such a change from its restore point
 *   changeclass/bulk      only with --with-bulk: every object of a class in a subtree, as a background job
 * each with the limitations asked for (Class, NewClass, Section, Subtree), none by default: the role's assignment
 * limits them as it limits the rest of the role (the Editors group: Content and Media). changeclass/administrate is
 * never granted here.
 *
 * Idempotent: a function the role holds already, in any form (also limited, or changeclass/*, or * / *), is not
 * added again. --revoke removes the role's changeclass/convert, restore and bulk policies, whatever their
 * limitations. plan() works on plain arrays (expEditorPolicies::policiesOf()'s form) and is tested without the kernel.
 */
class expChangeClassPolicies
{
    const ROLE_NAME = 'Editor';

    /**
     * The functions an editor role gets, in order.
     *
     * @param bool $withBulk
     * @return array[] each: function, reason
     */
    public static function definitions( $withBulk = false )
    {
        $list = array(
            array( 'function' => 'convert', 'reason' => 'change the class of an object they may edit, with a preview and a restore point (changeclass/convert)' ),
            array( 'function' => 'restore', 'reason' => 'undo such a change from its restore point (changeclass/restore)' ),
        );
        if ( $withBulk )
            $list[] = array( 'function' => 'bulk', 'reason' => 'change every object of a class in a subtree as a background job (changeclass/bulk)' );
        return $list;
    }

    /**
     * The functions --revoke takes away.
     *
     * @return string[]
     */
    public static function revocable()
    {
        return array( 'convert', 'restore', 'bulk' );
    }

    /**
     * Whether policies grant changeclass/<function> in any form.
     *
     * @param array[] $policies each: module, function
     * @param string $function
     * @return bool
     */
    public static function covers( array $policies, $function )
    {
        foreach ( $policies as $policy )
        {
            if ( $policy['module'] === '*' )
                return true;
            if ( $policy['module'] === 'changeclass' && ( $policy['function'] === '*' || $policy['function'] === $function ) )
                return true;
        }
        return false;
    }

    /**
     * What granting would do.
     *
     * @param array[] $policies the role's policies (module, function, limitations)
     * @param array $limitations identifier => values for the new policies (Class, NewClass, Section, Subtree)
     * @param bool $withBulk
     * @return array[] each: module, function, action (grant, skip), limitations, reason or why
     */
    public static function plan( array $policies, array $limitations = array(), $withBulk = false )
    {
        $plan = array();
        foreach ( self::definitions( $withBulk ) as $definition )
        {
            $entry = array( 'module' => 'changeclass', 'function' => $definition['function'], 'reason' => $definition['reason'],
                            'limitations' => self::limitationsFor( $definition['function'], $limitations ) );
            if ( self::covers( $policies, $definition['function'] ) )
            {
                $entry['action'] = 'skip';
                $entry['why'] = 'the role holds it already';
            }
            else
                $entry['action'] = 'grant';
            $plan[] = $entry;
        }
        return $plan;
    }

    /**
     * The limitations a function takes of those asked for (bulk takes Class and NewClass only).
     *
     * @param string $function
     * @param array $limitations
     * @return array
     */
    public static function limitationsFor( $function, array $limitations )
    {
        $allowed = $function === 'bulk' ? array( 'Class', 'NewClass' ) : array( 'Class', 'NewClass', 'Section', 'Subtree', 'Node' );
        $out = array();
        foreach ( $limitations as $identifier => $values )
        {
            $values = array_values( array_filter( array_map( 'strval', (array)$values ), 'strlen' ) );
            if ( in_array( $identifier, $allowed, true ) && $values )
                $out[$identifier] = $values;
        }
        return $out;
    }

    /**
     * The role's changeclass policies --revoke removes.
     *
     * @param array[] $policies the role's policies (with id)
     * @return array[] each: id, function, limitations
     */
    public static function revokePlan( array $policies )
    {
        $remove = array();
        foreach ( $policies as $policy )
        {
            if ( $policy['module'] === 'changeclass' && in_array( $policy['function'], self::revocable(), true ) )
                $remove[] = $policy;
        }
        return $remove;
    }

    /**
     * A line for limitations ("Class: article, folder; Section: 1").
     *
     * @param array $limitations
     * @return string
     */
    public static function describe( array $limitations )
    {
        if ( !$limitations )
            return 'no limitations (the role assignment limits it)';
        $parts = array();
        foreach ( $limitations as $identifier => $values )
            $parts[] = $identifier . ': ' . implode( ', ', (array)$values );
        return implode( '; ', $parts );
    }

    /**
     * The policies of a role as plain data.
     *
     * @param eZRole $role
     * @return array[]
     */
    public static function policiesOf( $role )
    {
        if ( class_exists( 'expEditorPolicies' ) )
            return expEditorPolicies::policiesOf( $role );
        $policies = array();
        foreach ( $role->policyList() as $policy )
        {
            $limitations = array();
            foreach ( (array)$policy->limitationList( false, true ) as $limitation )
            {
                $values = array();
                foreach ( (array)$limitation->attribute( 'values' ) as $value )
                    $values[] = (string)$value->attribute( 'value' );
                $limitations[(string)$limitation->attribute( 'identifier' )] = $values;
            }
            $policies[] = array( 'id' => (int)$policy->attribute( 'id' ), 'module' => (string)$policy->attribute( 'module_name' ),
                                 'function' => (string)$policy->attribute( 'function_name' ), 'limited' => count( $limitations ) > 0,
                                 'limitations' => $limitations );
        }
        return $policies;
    }

    /**
     * A role by id or name.
     *
     * @param string|int|null $role null: the Editor role
     * @return eZRole|null
     */
    public static function role( $role = null )
    {
        $role = $role === null || $role === '' ? self::ROLE_NAME : (string)$role;
        $found = ctype_digit( $role ) ? eZRole::fetch( (int)$role ) : eZRole::fetchByName( $role );
        return $found instanceof eZRole ? $found : null;
    }

    /**
     * Turns identifiers into the ids the limitations store: classes by identifier, sections by identifier, subtrees
     * by node id (to path strings).
     *
     * @param array $asked Class, NewClass (class identifiers or ids), Section (identifier or id), Subtree (node ids)
     * @param string[] $problems what could not be resolved
     * @return array identifier => values
     */
    public static function resolveLimitations( array $asked, array &$problems = array() )
    {
        $out = array();
        foreach ( array( 'Class', 'NewClass' ) as $identifier )
        {
            foreach ( isset( $asked[$identifier] ) ? (array)$asked[$identifier] : array() as $value )
            {
                $class = expChangeClassService::fetchClass( $value );
                if ( $class )
                    $out[$identifier][] = (string)$class->attribute( 'id' );
                else
                    $problems[] = "no class '$value'";
            }
        }
        foreach ( isset( $asked['Section'] ) ? (array)$asked['Section'] : array() as $value )
        {
            $section = ctype_digit( (string)$value ) ? eZSection::fetch( (int)$value ) : eZSection::fetchByIdentifier( (string)$value );
            if ( $section )
                $out['Section'][] = (string)$section->attribute( 'id' );
            else
                $problems[] = "no section '$value'";
        }
        foreach ( isset( $asked['Subtree'] ) ? (array)$asked['Subtree'] : array() as $value )
        {
            $node = eZContentObjectTreeNode::fetch( (int)$value );
            if ( $node instanceof eZContentObjectTreeNode )
                $out['Subtree'][] = (string)$node->attribute( 'path_string' );
            else
                $problems[] = "no node '$value'";
        }
        return $out;
    }

    /**
     * Gives a role the planned policies.
     *
     * @param eZRole $role
     * @param array[] $plan plan()
     * @return string[] the policies added (module/function)
     */
    public static function grant( $role, array $plan )
    {
        $added = array();
        foreach ( $plan as $entry )
        {
            if ( $entry['action'] !== 'grant' )
                continue;
            if ( $role->appendPolicy( $entry['module'], $entry['function'], $entry['limitations'] ) )
                $added[] = $entry['module'] . '/' . $entry['function'];
        }
        if ( $added )
            self::expire();
        return $added;
    }

    /**
     * Removes a role's changeclass policies.
     *
     * @param eZRole $role
     * @return string[] the policies removed (module/function)
     */
    public static function revoke( $role )
    {
        $removed = array();
        foreach ( self::revokePlan( self::policiesOf( $role ) ) as $policy )
        {
            $object = eZPolicy::fetch( (int)$policy['id'] );
            if ( $object instanceof eZPolicy )
            {
                $object->removeThis();
                $removed[] = 'changeclass/' . $policy['function'];
            }
        }
        if ( $removed )
            self::expire();
        return $removed;
    }

    /**
     * The role caches after a change of its policies.
     */
    protected static function expire()
    {
        eZRole::expireCache();
        if ( class_exists( 'eZContentCacheManager' ) )
            eZContentCacheManager::clearAllContentCache();
        if ( class_exists( 'eZUser' ) )
            eZUser::cleanupCache();
    }

    /**
     * The state of a role for the dashboard: whether it exists, which functions it holds, what granting would add.
     *
     * @param string|null $roleName
     * @return array exists, name, id, held (function => true), plan, revoke (policies)
     */
    public static function state( $roleName = null )
    {
        $role = self::role( $roleName );
        if ( !$role )
            return array( 'exists' => false, 'name' => $roleName ?: self::ROLE_NAME, 'id' => 0, 'held' => array(), 'plan' => array(), 'revoke' => array(), 'to_grant' => 0 );
        $policies = self::policiesOf( $role );
        $plan = self::plan( $policies );
        $held = array();
        foreach ( self::revocable() as $function )
            $held[$function] = self::covers( $policies, $function );
        $toGrant = 0;
        foreach ( $plan as $entry )
            if ( $entry['action'] === 'grant' )
                $toGrant++;
        return array( 'exists' => true, 'name' => (string)$role->attribute( 'name' ), 'id' => (int)$role->attribute( 'id' ),
                      'held' => $held, 'plan' => $plan, 'revoke' => self::revokePlan( $policies ), 'to_grant' => $toGrant );
    }
}
