<?php
/**
 * changeclass/dashboard[/(state)/<state>][/(user)/<id>]: the overview of class changes. The recent changes with their
 * restore points (the user's own; every user's with changeclass/administrate), a filter by state, the bulk change,
 * the commands, and with changeclass/administrate the policies of the Editor role.
 *
 * POST (303 back, the filter as view parameters):
 *   FilterButton (State)                              the filter
 *   RestoreButton / DiscardButton (RestorePointID)    as on changeclass/result
 *   GrantEditorPoliciesButton / RevokeEditorPoliciesButton (Role)
 *                                                     gives the role changeclass/convert and changeclass/restore, or
 *                                                     takes its changeclass policies away (changeclass/administrate;
 *                                                     "Verify it's you" asks first, site.ini [ReauthGroup_users])
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class Dashboard extends \Exponential\Runnable\ModuleView
{
    const STATES = array( 'applied', 'restored', 'discarded', 'failed', 'pending' );

    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();
        $admin = \expChangeClassAccess::holds( 'administrate' );
        $state = isset( $Params['State'] ) && in_array( $Params['State'], self::STATES, true ) ? $Params['State'] : '';

        if ( $http->hasPostVariable( 'FilterButton' ) )
        {
            $chosen = $http->hasPostVariable( 'State' ) ? (string)$http->postVariable( 'State' ) : '';
            return \expChangeClassView::redirect( $Module, 'changeclass/dashboard' . ( in_array( $chosen, self::STATES, true ) ? '/(state)/' . $chosen : '' ) );
        }
        $back = 'changeclass/dashboard' . ( $state !== '' ? '/(state)/' . $state : '' );
        if ( ( $http->hasPostVariable( 'RestoreButton' ) || $http->hasPostVariable( 'DiscardButton' ) ) && $http->hasPostVariable( 'RestorePointID' ) )
        {
            $data = \expChangeClassRestorePoint::load( (string)$http->postVariable( 'RestorePointID' ) );
            if ( $data && Result::canSee( $data ) )
            {
                if ( $http->hasPostVariable( 'RestoreButton' ) )
                    Result::restore( $data );
                else
                    Result::discard( $data );
            }
            return \expChangeClassView::redirect( $Module, $back );
        }
        if ( $admin && ( $http->hasPostVariable( 'GrantEditorPoliciesButton' ) || $http->hasPostVariable( 'RevokeEditorPoliciesButton' ) ) )
        {
            self::policies( $http->hasPostVariable( 'GrantEditorPoliciesButton' ), $http->hasPostVariable( 'Role' ) ? (string)$http->postVariable( 'Role' ) : '' );
            return \expChangeClassView::redirect( $Module, $back );
        }

        $list = array();
        foreach ( \expChangeClassRestorePoint::listRecent( 200, $admin ? null : (int)\eZUser::currentUserID() ) as $point )
        {
            if ( $state !== '' && $point['state'] !== $state )
                continue;
            $list[] = $point;
            if ( count( $list ) >= 50 )
                break;
        }
        $counts = array_fill_keys( self::STATES, 0 );
        foreach ( \expChangeClassRestorePoint::listRecent( 1000, $admin ? null : (int)\eZUser::currentUserID() ) as $point )
            if ( isset( $counts[$point['state']] ) )
                $counts[$point['state']]++;

        return \expChangeClassView::page( 'dashboard', array(
            'restore_points' => $list,
            'counts' => $counts,
            'state' => $state,
            'states' => self::STATES,
            'administrate' => $admin,
            'can_bulk' => \expChangeClassAccess::holds( 'bulk' ),
            'policies' => $admin ? \expChangeClassPolicies::state() : false,
            'jobs_available' => class_exists( 'expContentJob' ),
            'directory' => \expChangeClassRestorePoint::directory(),
        ), array( array( 'text' => \ezpI18n::tr( 'extension/expchangeclass', 'Change class' ), 'url' => false ) ) );
    }

    /**
     * Grants or revokes the editor policies and keeps the message.
     */
    public static function policies( $grant, $roleName )
    {
        $role = \expChangeClassPolicies::role( $roleName !== '' ? $roleName : null );
        if ( !$role )
        {
            \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'There is no role %role.', null, array( '%role' => $roleName !== '' ? $roleName : \expChangeClassPolicies::ROLE_NAME ) ) );
            return;
        }
        $name = (string)$role->attribute( 'name' );
        if ( $grant )
        {
            $done = \expChangeClassPolicies::grant( $role, \expChangeClassPolicies::plan( \expChangeClassPolicies::policiesOf( $role ) ) );
            $event = 'access.expchangeclass_policies.grant';
            $text = $done ? \ezpI18n::tr( 'extension/expchangeclass', 'The role %role may change classes now: %policies.', null, array( '%role' => $name, '%policies' => implode( ', ', $done ) ) )
                          : \ezpI18n::tr( 'extension/expchangeclass', 'The role %role held these policies already; nothing was added.', null, array( '%role' => $name ) );
        }
        else
        {
            $done = \expChangeClassPolicies::revoke( $role );
            $event = 'access.expchangeclass_policies.revoke';
            $text = $done ? \ezpI18n::tr( 'extension/expchangeclass', 'The role %role may no longer change classes: %policies removed.', null, array( '%role' => $name, '%policies' => implode( ', ', $done ) ) )
                          : \ezpI18n::tr( 'extension/expchangeclass', 'The role %role had no class change policies; nothing was removed.', null, array( '%role' => $name ) );
        }
        if ( class_exists( 'expAudit' ) && $done )
            \expAudit::event( $event, array( 'object' => array( 'type' => 'role', 'id' => (int)$role->attribute( 'id' ), 'name' => $name ),
                                             'after' => array( 'policies' => implode( ', ', $done ) ) ) );
        \expChangeClassView::flash( 'ok', $text );
    }
}

}
