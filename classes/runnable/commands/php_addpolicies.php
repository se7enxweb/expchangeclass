<?php
/**
 * exp:addchangeclasspolicies (ext:expchangeclass:addpolicies): gives the Editor role (or --role) the policies to change
 * classes, in the style of exp:addeditorpolicies. A dry run unless --apply; nothing is added twice.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

namespace Exponential\Command\Extension\Expchangeclass
{

class Addpolicies extends \Exponential\Runnable\Command
{
    public function run()
    {
        $cli = $this->cli();
        $script = $this->script( array(
            'description' => "Give the Editor role the policies to change the class of content they may edit:\n"
                . "  changeclass/convert   change the class of one object, with the preview and a restore point\n"
                . "  changeclass/restore   undo such a change from its restore point\n"
                . "  changeclass/bulk      only with --with-bulk: every object of a class in a subtree, in the background\n"
                . "Without limitations, the role's assignment limits them as it limits the rest of the role (the Editors\n"
                . "group: Content and Media); --class, --new-class, --section and --subtree add limitations. A function\n"
                . "the role holds already in any form (also limited, changeclass/* or */*) is left alone, so the command\n"
                . "can run again. changeclass/administrate (the overview of every user's changes, the policy button) is\n"
                . "never given. --revoke takes the role's changeclass/convert, restore and bulk policies away.\n"
                . "Without --apply nothing is written. Prints PASS or FAIL at the end.\n\n"
                . "Example: ./console exp:addchangeclasspolicies\n"
                . "         ./console exp:addchangeclasspolicies --apply\n"
                . "         ./console exp:addchangeclasspolicies --apply --role=Redakteur --class=article --new-class=blog_post\n"
                . "         ./console exp:addchangeclasspolicies --apply --revoke",
            'use-session' => false,
            'use-modules' => true,
            'use-extensions' => true
        ) );
        $options = $this->startup(
            '[apply][dry-run][role:][revoke][with-bulk][class:*][new-class:*][section:*][subtree:*]',
            '',
            array(
                'apply' => 'Write the changes (the default is a dry run that lists them)',
                'dry-run' => 'The default, kept for scripts that pass it',
                'role' => 'The role (ID or name); default Editor',
                'revoke' => 'Take the role\'s changeclass policies away instead',
                'with-bulk' => 'Also give changeclass/bulk',
                'class' => 'Limitation Class: only objects of this class (identifier), may be repeated',
                'new-class' => 'Limitation NewClass: only into this class (identifier), may be repeated',
                'section' => 'Limitation Section (identifier or ID), may be repeated',
                'subtree' => 'Limitation Subtree (node ID), may be repeated',
            )
        );
        $apply = self::applyRequested( $options );
        if ( $apply === null )
        {
            $cli->error( 'FAIL: --apply and --dry-run exclude each other; without --apply the command changes nothing' );
            $script->shutdown( 2 );
            return 2;
        }
        $roleName = !empty( $options['role'] ) ? (string)$options['role'] : \expChangeClassPolicies::ROLE_NAME;
        $role = \expChangeClassPolicies::role( $roleName );
        if ( !$role )
        {
            $cli->error( "FAIL: no role '$roleName'" );
            $script->shutdown( 1 );
            return 1;
        }
        $label = sprintf( '%s (%d)', $role->attribute( 'name' ), $role->attribute( 'id' ) );
        $policies = \expChangeClassPolicies::policiesOf( $role );

        if ( !empty( $options['revoke'] ) )
        {
            $remove = \expChangeClassPolicies::revokePlan( $policies );
            foreach ( $remove as $policy )
                $cli->output( ( $apply ? 'remove      ' : 'would remove' ) . sprintf( ' %-22s policy %d, %s', 'changeclass/' . $policy['function'], $policy['id'],
                                                                                         \expChangeClassPolicies::describe( $policy['limitations'] ) ) );
            if ( $apply && $remove )
            {
                $removed = \expChangeClassPolicies::revoke( $role );
                if ( \expChangeClassPolicies::revokePlan( \expChangeClassPolicies::policiesOf( \eZRole::fetch( (int)$role->attribute( 'id' ) ) ) ) )
                {
                    $cli->error( "FAIL: $label still holds changeclass policies" );
                    $script->shutdown( 1 );
                    return 1;
                }
                self::audit( 'access.expchangeclass_policies.revoke', $role, $removed );
            }
            $cli->output();
            $cli->output( 'PASS: ' . count( $remove ) . ' polic' . ( count( $remove ) === 1 ? 'y' : 'ies' )
                          . ( $apply ? ' removed from ' : ' would be removed from ' ) . $label . ( $apply ? '' : ' (dry run, nothing changed; --apply writes)' ) );
            $script->shutdown( 0 );
            return 0;
        }

        $problems = array();
        $limitations = \expChangeClassPolicies::resolveLimitations( array(
            'Class' => !empty( $options['class'] ) ? (array)$options['class'] : array(),
            'NewClass' => !empty( $options['new-class'] ) ? (array)$options['new-class'] : array(),
            'Section' => !empty( $options['section'] ) ? (array)$options['section'] : array(),
            'Subtree' => !empty( $options['subtree'] ) ? (array)$options['subtree'] : array(),
        ), $problems );
        if ( $problems )
        {
            $cli->error( 'FAIL: ' . implode( '; ', $problems ) );
            $script->shutdown( 1 );
            return 1;
        }

        $plan = \expChangeClassPolicies::plan( $policies, $limitations, !empty( $options['with-bulk'] ) );
        $granted = 0;
        foreach ( $plan as $entry )
        {
            $name = sprintf( '%-22s', $entry['module'] . '/' . $entry['function'] );
            if ( $entry['action'] !== 'grant' )
            {
                $cli->output( "skip         $name {$entry['why']}" );
                continue;
            }
            $cli->output( ( $apply ? 'add         ' : 'would add   ' ) . " $name " . $entry['reason'] . '; ' . \expChangeClassPolicies::describe( $entry['limitations'] ) );
            $granted++;
        }
        if ( $apply && $granted )
        {
            $added = \expChangeClassPolicies::grant( $role, $plan );
            // Check what was stored: every planned function is there now.
            $after = \expChangeClassPolicies::plan( \expChangeClassPolicies::policiesOf( \eZRole::fetch( (int)$role->attribute( 'id' ) ) ), $limitations, !empty( $options['with-bulk'] ) );
            foreach ( $after as $entry )
            {
                if ( $entry['action'] === 'grant' )
                {
                    $cli->error( "FAIL: changeclass/{$entry['function']} is still missing from $label" );
                    $script->shutdown( 1 );
                    return 1;
                }
            }
            self::audit( 'access.expchangeclass_policies.grant', $role, $added );
        }
        $cli->output();
        if ( $apply )
            $cli->output( "PASS: $granted polic" . ( $granted === 1 ? 'y' : 'ies' ) . " added to $label" );
        else
            $cli->output( "PASS: $granted polic" . ( $granted === 1 ? 'y' : 'ies' ) . " would be added to $label (dry run, nothing changed; --apply writes)" );
        $script->shutdown( 0 );
        return 0;
    }

    protected static function audit( $name, $role, array $policies )
    {
        if ( !class_exists( 'expAudit' ) || !$policies )
            return;
        \expAudit::event( $name, array( 'object' => array( 'type' => 'role', 'id' => (int)$role->attribute( 'id' ), 'name' => (string)$role->attribute( 'name' ) ),
                                        'after' => array( 'policies' => implode( ', ', $policies ) ) ) );
    }
}

}
