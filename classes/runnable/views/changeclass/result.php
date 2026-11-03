<?php
/**
 * changeclass/result/<RestorePointID>: step 3, what a class change did, and its restore point.
 *
 * POST (303 back here):
 *   RestoreButton  puts the object back as it was (changeclass/restore on the object, NewClass the old class; or
 *                  changeclass/administrate). Refused, with the reason, when the object was edited since.
 *   DiscardButton  lets the dropped data go for good (the user who made the change, or changeclass/administrate)
 * Both ask "Verify it's you" first (site.ini [ReauthGroup_changeclass]).
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 */

namespace Exponential\View\Extension\Expchangeclass\Changeclass
{

class Result extends \Exponential\Runnable\ModuleView
{
    public function run( array $scope )
    {
        $Params = $scope['Params'];
        $Module = $Params['Module'];
        $http = \eZHTTPTool::instance();
        $id = isset( $Params['RestorePointID'] ) ? (string)$Params['RestorePointID'] : '';
        $data = \expChangeClassRestorePoint::load( $id );
        if ( !$data || !self::canSee( $data ) )
            return $Module->handleError( \eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        $here = 'changeclass/result/' . $id;

        if ( $http->hasPostVariable( 'RestoreButton' ) )
        {
            self::restore( $data );
            return \expChangeClassView::redirect( $Module, $here );
        }
        if ( $http->hasPostVariable( 'DiscardButton' ) )
        {
            self::discard( $data );
            return \expChangeClassView::redirect( $Module, $here );
        }

        $object = \eZContentObject::fetch( (int)$data['object']['id'] );
        $node = $object instanceof \eZContentObject ? $object->mainNode() : null;
        $problem = \expChangeClassRestorePoint::restoreProblem( $data );
        return \expChangeClassView::page( 'result', array(
            'restore_point' => \expChangeClassRestorePoint::summary( $data ),
            'object' => $object,
            'node' => $node,
            'restore_problem' => $problem === null ? '' : \ezpI18n::tr( 'extension/expchangeclass', $problem ),
            'can_restore' => $problem === null && self::canRestore( $data ),
            'can_discard' => in_array( $data['state'], array( \expChangeClassRestorePoint::APPLIED ), true ) && self::canDiscard( $data ),
            'command' => './console exp:changeclass --restore=' . $id,
            'bulk_count' => $object instanceof \eZContentObject && \expChangeClassAccess::holds( 'bulk' )
                          ? \expChangeClassService::countInSubtree( (int)$data['source_class']['id'], '/1/' ) : 0,
            'can_bulk' => \expChangeClassAccess::holds( 'bulk' ),
        ), \expChangeClassView::path( \ezpI18n::tr( 'extension/expchangeclass', 'Result' ), $node ) );
    }

    /**
     * The user who made the change, or one with changeclass/administrate.
     */
    public static function canSee( array $data )
    {
        return (int)$data['user_id'] === (int)\eZUser::currentUserID() || \expChangeClassAccess::holds( 'administrate' );
    }

    public static function canDiscard( array $data )
    {
        return self::canSee( $data );
    }

    public static function canRestore( array $data )
    {
        if ( \expChangeClassAccess::holds( 'administrate' ) )
            return true;
        $object = \eZContentObject::fetch( (int)$data['object']['id'] );
        return $object instanceof \eZContentObject && $object->canEdit()
            && \expChangeClassAccess::can( 'restore', $object, (int)$data['source_class']['id'] );
    }

    /**
     * Restores and keeps the message for the next page.
     *
     * @return bool
     */
    public static function restore( array $data )
    {
        if ( !self::canRestore( $data ) )
        {
            \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'Your role does not allow undoing this change (policy changeclass/restore).' ) );
            return false;
        }
        $error = null;
        $restored = \expChangeClassRestorePoint::restore( $data, $error );
        if ( !$restored )
        {
            \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'The change was not undone.' ), array( \ezpI18n::tr( 'extension/expchangeclass', (string)$error ) ) );
            \expChangeClassService::auditRestorePoint( 'content.expchangeclass_object.restore', $data, array( 'result' => 'refused', 'reason' => 'state' ) );
            return false;
        }
        \expChangeClassService::auditRestorePoint( 'content.expchangeclass_object.restore', $data );
        \expChangeClassView::flash( 'ok', \ezpI18n::tr( 'extension/expchangeclass', 'The object is of the class %class again, with all its attributes as they were.', null,
                                                       array( '%class' => $data['source_class']['name'] ) ) );
        return true;
    }

    public static function discard( array $data )
    {
        if ( !self::canDiscard( $data ) || $data['state'] !== \expChangeClassRestorePoint::APPLIED )
        {
            \expChangeClassView::flash( 'bad', \ezpI18n::tr( 'extension/expchangeclass', 'This restore point cannot be discarded.' ) );
            return false;
        }
        \expChangeClassRestorePoint::discard( $data );
        \expChangeClassService::auditRestorePoint( 'content.expchangeclass_restorepoint.discard', $data );
        \expChangeClassView::flash( 'ok', \ezpI18n::tr( 'extension/expchangeclass', 'The restore point was discarded: the dropped data is gone for good.' ) );
        return true;
    }
}

}
