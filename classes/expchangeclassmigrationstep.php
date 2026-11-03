<?php
/**
 * File containing the expChangeClassMigrationStep class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The step type "change_class" of YAML migrations (Exponential 6.0.15 and later, settings/migration.ini.append.php
 * [StepSettings] StepTypeList[change_class]): changes the class of objects with a mapping, exactly as the
 * administration does (the same plan, restore point and transaction per object).
 *
 *   - type: change_class
 *     objects: [ "remote:about-us", 57 ]   the objects (id, remote:, path:)
 *     from: folder                         optional: only objects of this class (others fail the step)
 *     class: landing_page                  the class they become
 *     mapping: { title: name, body: description }   new attribute: old attribute ('' leaves it empty);
 *                                          missing: the proposed mapping (same identifier, then same datatype)
 *
 * plan() lists the plan of each object (the dry run); apply() changes them and fails the step on the first object
 * whose plan refuses or whose change is rolled back. The values for "references:": changed (how many) and
 * restore_points (comma-separated), which ./console exp:changeclass --restore=<id> undoes.
 */
class expChangeClassMigrationStep extends expMigrationStepTypeBase
{
    public function name()
    {
        return 'change_class';
    }

    public function schema()
    {
        return array(
            'description' => 'Changes the class of objects, mapping each attribute of the new class onto one of the old (expchangeclass)',
            'keys' => array(
                'objects' => array( 'type' => 'list', 'required' => true, 'description' => 'the objects (id, remote:, path:)' ),
                'from' => array( 'type' => 'identifier', 'kind' => 'class', 'description' => 'only objects of this class' ),
                'class' => array( 'type' => 'identifier', 'kind' => 'class', 'required' => true, 'description' => 'the class the objects become' ),
                'mapping' => array( 'type' => 'map', 'description' => 'new attribute identifier: old attribute identifier (\'\' leaves it empty); missing: the proposed mapping' ),
            ),
            'values' => array(
                'changed' => 'how many objects were changed',
                'restore_points' => 'the restore points of the changes, comma-separated',
            ),
            'example' => "- type: change_class\n  objects: [ \"remote:about-us\" ]\n  from: folder\n  class: landing_page\n  mapping: { title: name, body: description }\n",
        );
    }

    public function caches( array $step )
    {
        return array( 'content', 'template-block' );
    }

    /**
     * The objects and classes of a step, resolved.
     *
     * @return array objects (eZContentObject[]), class, from, problems
     */
    protected function resolve( array $step )
    {
        $problems = array();
        $objects = array();
        foreach ( (array)$step['objects'] as $value )
        {
            $id = expMigrationIdentifier::find( 'object', $value );
            $object = $id ? eZContentObject::fetch( $id ) : null;
            if ( $object instanceof eZContentObject )
                $objects[] = $object;
            else
                $problems[] = 'no object ' . json_encode( $value );
        }
        $classID = expMigrationIdentifier::find( 'class', $step['class'] );
        if ( !$classID )
            $problems[] = 'no class ' . json_encode( $step['class'] );
        $fromID = null;
        if ( isset( $step['from'] ) && $step['from'] !== null && $step['from'] !== '' )
        {
            $fromID = expMigrationIdentifier::find( 'class', $step['from'] );
            if ( !$fromID )
                $problems[] = 'no class ' . json_encode( $step['from'] );
        }
        foreach ( $objects as $object )
        {
            if ( $fromID && (int)$object->attribute( 'contentclass_id' ) !== (int)$fromID )
                $problems[] = 'object ' . $object->attribute( 'id' ) . ' is of the class ' . $object->attribute( 'class_identifier' ) . ', not ' . $step['from'];
        }
        return array( 'objects' => $objects, 'class' => $classID, 'problems' => $problems );
    }

    public function plan( array $step, $context )
    {
        $resolved = $this->resolve( $step );
        $lines = $resolved['problems'];
        if ( $lines )
            return $lines;
        $mapping = isset( $step['mapping'] ) && is_array( $step['mapping'] ) ? $step['mapping'] : null;
        foreach ( $resolved['objects'] as $object )
        {
            $plan = expChangeClassService::preview( $object, $resolved['class'], $mapping );
            $line = 'change object ' . $object->attribute( 'id' ) . ' (' . $object->attribute( 'class_identifier' ) . ') into '
                  . $plan['facts']['dest_class']['identifier'] . ': ';
            $pairs = array();
            foreach ( $plan['rows'] as $row )
                $pairs[] = $row['identifier'] . ' <- ' . ( $row['source'] !== '' ? $row['source'] . ' (' . $row['kind'] . ')' : 'empty' );
            $line .= implode( ', ', $pairs );
            foreach ( $plan['dropped'] as $dropped )
                $line .= '; drops ' . $dropped['identifier'];
            $lines[] = $line;
            foreach ( $plan['errors'] as $error )
                $lines[] = '  refused: ' . expChangeClassPlanner::render( $error );
            foreach ( $plan['warnings'] as $warning )
                $lines[] = '  note: ' . expChangeClassPlanner::render( $warning );
        }
        return $lines;
    }

    public function apply( array $step, $context )
    {
        $resolved = $this->resolve( $step );
        if ( $resolved['problems'] )
            throw new RuntimeException( implode( '; ', $resolved['problems'] ) );
        $mapping = isset( $step['mapping'] ) && is_array( $step['mapping'] ) ? $step['mapping'] : null;
        $lines = array();
        $points = array();
        foreach ( $resolved['objects'] as $object )
        {
            $result = expChangeClassService::apply( $object, $resolved['class'], $mapping, array( 'via' => 'migration' ) );
            if ( !$result['ok'] )
                throw new RuntimeException( 'object ' . $object->attribute( 'id' ) . ': ' . $result['error'] );
            $points[] = $result['restore_point'];
            $lines[] = 'changed object ' . $object->attribute( 'id' ) . ' (restore point ' . $result['restore_point'] . ')';
            if ( is_object( $context ) && method_exists( $context, 'touchObject' ) )
                $context->touchObject( array( (int)$object->attribute( 'id' ) ) );
        }
        return self::result( $lines, array( 'changed' => count( $points ), 'restore_points' => implode( ',', $points ) ) );
    }
}
