<?php
/**
 * File containing the expChangeClassJob class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The content job "changeclass" (content.ini [ContentJobSettings] JobTypes[changeclass], Exponential 6.0.15 and
 * later): every published object of a class in a subtree changed into another class with one mapping, in the
 * background, with the progress page content/job/<id>, cancel and resume.
 *
 * One object per batch: each object is changed by expChangeClassService::apply(), with its own plan, restore point
 * and transaction, as the requesting user (so changeclass/convert and content/edit are checked per object). An
 * object whose plan refuses, or whose change is rolled back, is skipped with the reason and remembered in the
 * checkpoint, so the job goes on and never tries it again; the worker's batch transaction is left to commit empty.
 * Idempotent: a changed object is no longer of the source class and is not found again.
 *
 * params: source_class_id, dest_class_id, mapping (destination identifier => source identifier), node_id, path
 * result: changed, skipped (object id => reason), restore_points
 */
class expChangeClassJob implements expContentJobType
{
    /** How many skipped objects the result lists with their reason. */
    const MAX_LISTED = 200;

    public function validate( array $params, eZUser $user )
    {
        $source = eZContentClass::fetch( isset( $params['source_class_id'] ) ? (int)$params['source_class_id'] : 0 );
        $dest = eZContentClass::fetch( isset( $params['dest_class_id'] ) ? (int)$params['dest_class_id'] : 0 );
        if ( !$source instanceof eZContentClass || !$dest instanceof eZContentClass )
            throw new expContentJobException( 'The class does not exist.' );
        if ( (int)$source->attribute( 'id' ) === (int)$dest->attribute( 'id' ) )
            throw new expContentJobException( 'The two classes are the same.' );
        $node = eZContentObjectTreeNode::fetch( isset( $params['node_id'] ) ? (int)$params['node_id'] : 0 );
        if ( !$node instanceof eZContentObjectTreeNode )
            throw new expContentJobException( 'The node does not exist.' );
        $access = $user->hasAccessTo( 'changeclass', 'bulk' );
        if ( !expChangeClassAccess::allows( (array)$access, array( 'class_id' => (int)$source->attribute( 'id' ), 'new_class_id' => (int)$dest->attribute( 'id' ),
                                                                     'section_id' => 0, 'node_ids' => array(), 'path_strings' => array() ) ) )
            throw new expContentJobException( 'Your role does not allow changing these classes in the background (policy changeclass/bulk).' );
        $mapping = array();
        foreach ( isset( $params['mapping'] ) ? (array)$params['mapping'] : array() as $to => $from )
            $mapping[(string)$to] = (string)$from;
        return array( 'source_class_id' => (int)$source->attribute( 'id' ), 'dest_class_id' => (int)$dest->attribute( 'id' ),
                      'mapping' => $mapping, 'node_id' => (int)$node->attribute( 'node_id' ),
                      'path' => (string)$node->attribute( 'path_string' ) );
    }

    public function countNodes( array $params )
    {
        return expChangeClassService::countInSubtree( $params['source_class_id'], $params['path'] );
    }

    public function locks( array $params )
    {
        return array( array( 'path' => $params['path'], 'mode' => expContentJobLock::SUBTREE ) );
    }

    public function describe( array $params )
    {
        $source = eZContentClass::fetch( (int)$params['source_class_id'] );
        $dest = eZContentClass::fetch( (int)$params['dest_class_id'] );
        return 'Change ' . ( $source ? $source->attribute( 'identifier' ) : $params['source_class_id'] ) . ' into '
             . ( $dest ? $dest->attribute( 'identifier' ) : $params['dest_class_id'] ) . ' below node ' . $params['node_id'];
    }

    public function prepare( expContentJob $job )
    {
        $checkpoint =& $job->checkpoint();
        $checkpoint += array( 'changed' => 0, 'skipped' => array(), 'restore_points' => array() );
    }

    public function runBatch( expContentJob $job, $batchSize )
    {
        $params = $job->params();
        $checkpoint =& $job->checkpoint();
        $ids = expChangeClassService::objectsInSubtree( $params['source_class_id'], $params['path'], 1, array_keys( $checkpoint['skipped'] ) );
        if ( !$ids )
            return array( 'done' => 0, 'finished' => true, 'message' => 'Changed ' . $checkpoint['changed'] . ', skipped ' . count( $checkpoint['skipped'] ) );
        $objectID = $ids[0];
        // the job's own lock covers the subtree: the object's check would find it
        $result = expChangeClassService::apply( $objectID, $params['dest_class_id'], $params['mapping'], array( 'via' => 'job', 'job_id' => $job->id(), 'ignore_job' => $job->id() ) );
        $db = eZDB::instance();
        if ( $db->transactionCounter() === 0 )
            $db->begin(); // a rolled back change ended the worker's transaction; it commits this empty one
        if ( $result['ok'] )
        {
            $checkpoint['changed']++;
            if ( count( $checkpoint['restore_points'] ) < self::MAX_LISTED )
                $checkpoint['restore_points'][] = $result['restore_point'];
            $message = 'Changed object ' . $objectID;
        }
        else
        {
            $checkpoint['skipped'][$objectID] = $result['error'];
            $message = 'Skipped object ' . $objectID . ': ' . $result['error'];
        }
        return array( 'done' => 1, 'finished' => false, 'message' => $message );
    }

    public function afterBatch( expContentJob $job )
    {
    }

    public function finish( expContentJob $job )
    {
        $checkpoint =& $job->checkpoint();
        $job->setResult( 'changed', (int)$checkpoint['changed'] );
        $job->setResult( 'skipped', count( $checkpoint['skipped'] ) );
        $listed = array();
        foreach ( array_slice( $checkpoint['skipped'], 0, self::MAX_LISTED, true ) as $id => $reason )
            $listed[] = $id . ': ' . $reason;
        $job->setResult( 'skipped_objects', $listed );
        $job->setResult( 'restore_points', $checkpoint['restore_points'] );
    }
}
