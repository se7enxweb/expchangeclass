<?php
/**
 * File containing the expChangeClassAccess class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The policies of the changeclass module and their limitations.
 *
 *   changeclass/convert       change the class of one object. Limitations: Class (the object's class now),
 *                             NewClass (the class it becomes), Section, Node, Subtree; the role's assignment limits it
 *                             as well (User_Section, User_Subtree)
 *   changeclass/restore       undo a change from its restore point (the same limitations, NewClass being the class
 *                             the object gets back)
 *   changeclass/bulk          change every object of a class in a subtree at once, as a background job (Class,
 *                             NewClass); each object is still checked against changeclass/convert
 *   changeclass/administrate  the overview of every user's changes, discarding restore points, and the button that
 *                             gives the Editor role the policies above
 *
 * A change also needs content/edit on the object. matches() works on plain arrays and is tested without the kernel.
 */
class expChangeClassAccess
{
    /**
     * Whether one policy's limitations allow a subject.
     *
     * @param array $limitations identifier => values, as eZUser::hasAccessTo() gives them for a policy
     * @param array $subject class_id, new_class_id, section_id, node_ids (int[]), path_strings (string[])
     * @return bool
     */
    public static function matches( array $limitations, array $subject )
    {
        foreach ( $limitations as $identifier => $values )
        {
            $values = array_map( 'strval', (array)$values );
            switch ( $identifier )
            {
                case 'Class':
                    if ( !in_array( (string)$subject['class_id'], $values, true ) )
                        return false;
                    break;
                case 'NewClass':
                    if ( !isset( $subject['new_class_id'] ) || $subject['new_class_id'] === null )
                        break; // not known yet (the class select): the destination is checked when it is chosen
                    if ( !in_array( (string)$subject['new_class_id'], $values, true ) )
                        return false;
                    break;
                case 'Section':
                case 'User_Section':
                    if ( !in_array( (string)$subject['section_id'], $values, true ) )
                        return false;
                    break;
                case 'Node':
                    if ( !array_intersect( array_map( 'strval', (array)$subject['node_ids'] ), $values ) )
                        return false;
                    break;
                case 'Subtree':
                case 'User_Subtree':
                    $inside = false;
                    foreach ( (array)$subject['path_strings'] as $path )
                    {
                        foreach ( $values as $subtree )
                        {
                            if ( $subtree !== '' && strpos( (string)$path, $subtree ) === 0 )
                            {
                                $inside = true;
                                break 2;
                            }
                        }
                    }
                    if ( !$inside )
                        return false;
                    break;
                default:
                    // a limitation this module does not know refuses, rather than allowing more than was meant
                    return false;
            }
        }
        return true;
    }

    /**
     * Whether eZUser::hasAccessTo()'s answer allows a subject.
     *
     * @param array $access accessWord, policies
     * @param array $subject see matches()
     * @return bool
     */
    public static function allows( array $access, array $subject )
    {
        if ( !isset( $access['accessWord'] ) || $access['accessWord'] === 'no' )
            return false;
        if ( $access['accessWord'] === 'yes' )
            return true;
        foreach ( isset( $access['policies'] ) ? (array)$access['policies'] : array() as $limitations )
        {
            if ( self::matches( (array)$limitations, $subject ) )
                return true;
        }
        return false;
    }

    /**
     * The subject of an object for matches().
     *
     * @param eZContentObject $object
     * @param int|null $newClassID
     * @return array
     */
    public static function subject( $object, $newClassID = null )
    {
        $nodeIDs = array();
        $paths = array();
        foreach ( (array)$object->assignedNodes( false ) as $node )
        {
            $nodeIDs[] = (int)$node['node_id'];
            $paths[] = (string)$node['path_string'];
        }
        return array( 'class_id' => (int)$object->attribute( 'contentclass_id' ),
                      'new_class_id' => $newClassID === null ? null : (int)$newClassID,
                      'section_id' => (int)$object->attribute( 'section_id' ),
                      'node_ids' => $nodeIDs, 'path_strings' => $paths );
    }

    /**
     * Whether a user may use a function of the module on an object.
     *
     * @param string $function convert, restore or bulk
     * @param eZContentObject $object
     * @param int|null $newClassID
     * @param eZUser|null $user default: the current user
     * @return bool
     */
    public static function can( $function, $object, $newClassID = null, $user = null )
    {
        $user = $user ?: eZUser::currentUser();
        $access = $user->hasAccessTo( 'changeclass', $function );
        return self::allows( (array)$access, self::subject( $object, $newClassID ) );
    }

    /**
     * Whether a user holds a function of the module at all (in any limitation).
     *
     * @param string $function
     * @param eZUser|null $user
     * @return bool
     */
    public static function holds( $function, $user = null )
    {
        $user = $user ?: eZUser::currentUser();
        $access = $user->hasAccessTo( 'changeclass', $function );
        return isset( $access['accessWord'] ) && $access['accessWord'] !== 'no';
    }
}
