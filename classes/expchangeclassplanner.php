<?php
/**
 * File containing the expChangeClassPlanner class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * What a class change would do, worked out on plain arrays before anything is written: which datatypes convert into
 * which (conversionKind()), the mapping proposed for two classes (proposeMapping()), and the plan of one object
 * (plan()): a row per attribute of the new class with its source and how its data is carried over, the attributes
 * whose data would be dropped, the problems that refuse the change (errors) and those an editor should know about
 * (warnings), each with a code, an English text with %placeholders and its values, so the templates translate it and
 * the command prints it.
 *
 * Nothing here reads the database or the settings: expChangeClassService gathers the facts and the rules
 * (rulesFromSettings()), so the planner is tested without the kernel (tests/expchangeclassplannertest.php).
 *
 * The facts of plan():
 *   source_class, dest_class  id, identifier, name, is_container, attributes (each: id, identifier, name, datatype,
 *                             required, translatable)
 *   object                    id, name, status (0 draft, 1 published, 2 archived), is_user, locked, children_count,
 *                             languages (codes), versions (each: version, status, creator_id), current_user_id
 *   values                    source identifier => language => array( has_content, text )
 *   reverse_relations         how many objects relate to this one through an attribute
 *   job                       a content job working on the object's part of the tree (its description), or ''
 *   can_edit                  the user may edit the object (content/edit)
 */
class expChangeClassPlanner
{
    const SAME = 'same';
    const SIMPLE = 'simple';
    const CONVERTER = 'converter';
    const EMPTY_ATTRIBUTE = 'empty';
    const COPY = 'copy';

    /** Version statuses of eZContentObjectVersion. */
    const VERSION_DRAFT = 0;
    const VERSION_PUBLISHED = 1;
    const VERSION_PENDING = 2;
    const VERSION_ARCHIVED = 3;
    const VERSION_REJECTED = 4;
    const VERSION_INTERNAL_DRAFT = 5;

    /** Object status of eZContentObject: in the trash. */
    const OBJECT_ARCHIVED = 2;

    /**
     * The rules of changeclass.ini as plain data.
     *
     * @param eZINI $ini changeclass.ini
     * @return array simple (source => destinations), converters (source => destinations, callable), unsupported
     */
    public static function rulesFromSettings( $ini )
    {
        $rules = array( 'simple' => array(), 'converters' => array(), 'unsupported' => array() );
        if ( $ini->hasVariable( 'General', 'UnsupportedDataTypeArray' ) )
            $rules['unsupported'] = array_values( array_filter( array_map( 'trim', (array)$ini->variable( 'General', 'UnsupportedDataTypeArray' ) ) ) );
        if ( $ini->hasVariable( 'SimpleConversion', 'SupportedConversion' ) )
        {
            foreach ( (array)$ini->variable( 'SimpleConversion', 'SupportedConversion' ) as $line )
            {
                $parts = array_map( 'trim', explode( ';', (string)$line ) );
                if ( count( $parts ) < 2 || $parts[0] === '' || $parts[1] === '' )
                    continue;
                $rules['simple'][$parts[0]][] = $parts[1];
            }
        }
        foreach ( $ini->groups() as $name => $group )
        {
            if ( !isset( $group['SupportedDestination'] ) )
                continue;
            $destinations = array_values( array_filter( array_map( 'trim', (array)$group['SupportedDestination'] ) ) );
            $callable = ( isset( $group['Class'] ) && trim( $group['Class'] ) !== '' ? trim( $group['Class'] ) . '::' : '' )
                      . ( isset( $group['Function'] ) ? trim( $group['Function'] ) : '' );
            $rules['converters'][$name] = array( 'destinations' => $destinations, 'callable' => $callable );
        }
        return $rules;
    }

    /**
     * How data of one datatype is carried into another: same (the datatypes are equal), simple (listed in
     * [SimpleConversion]), converter (a converter of changeclass.ini does it), or null (it cannot be).
     *
     * @param string $source
     * @param string $destination
     * @param array $rules rulesFromSettings()
     * @return string|null
     */
    public static function conversionKind( $source, $destination, array $rules )
    {
        if ( $source === $destination )
            return self::SAME;
        if ( isset( $rules['simple'][$source] ) && in_array( $destination, $rules['simple'][$source], true ) )
            return self::SIMPLE;
        if ( isset( $rules['converters'][$source] ) && in_array( $destination, $rules['converters'][$source]['destinations'], true ) )
            return self::CONVERTER;
        return null;
    }

    /**
     * The mapping offered first: for each attribute of the new class the source attribute with the same identifier
     * when its data can be carried over, else the first unused source attribute of the same datatype, else the first
     * unused one that converts, else none (''). A source attribute is proposed once.
     *
     * @param array[] $sourceAttributes each: identifier, datatype
     * @param array[] $destAttributes each: identifier, datatype
     * @param array $rules
     * @return array destination identifier => source identifier or ''
     */
    public static function proposeMapping( array $sourceAttributes, array $destAttributes, array $rules )
    {
        $used = array();
        $mapping = array();
        $byIdentifier = array();
        foreach ( $sourceAttributes as $attribute )
            $byIdentifier[$attribute['identifier']] = $attribute;

        // pass 1: same identifier
        foreach ( $destAttributes as $dest )
        {
            $mapping[$dest['identifier']] = '';
            if ( isset( $byIdentifier[$dest['identifier']] ) &&
                 self::conversionKind( $byIdentifier[$dest['identifier']]['datatype'], $dest['datatype'], $rules ) !== null )
            {
                $mapping[$dest['identifier']] = $dest['identifier'];
                $used[$dest['identifier']] = true;
            }
        }
        // pass 2: same datatype, then pass 3: anything that converts
        foreach ( array( true, false ) as $sameOnly )
        {
            foreach ( $destAttributes as $dest )
            {
                if ( $mapping[$dest['identifier']] !== '' )
                    continue;
                foreach ( $sourceAttributes as $source )
                {
                    if ( isset( $used[$source['identifier']] ) )
                        continue;
                    $kind = self::conversionKind( $source['datatype'], $dest['datatype'], $rules );
                    if ( $kind === null || ( $sameOnly && $kind !== self::SAME ) )
                        continue;
                    $mapping[$dest['identifier']] = $source['identifier'];
                    $used[$source['identifier']] = true;
                    break;
                }
            }
        }
        return $mapping;
    }

    /**
     * The source attributes a destination attribute may take (for the select of the mapping form).
     *
     * @param array $dest identifier, datatype
     * @param array[] $sourceAttributes
     * @param array $rules
     * @return array[] each: identifier, name, datatype, kind
     */
    public static function candidates( array $dest, array $sourceAttributes, array $rules )
    {
        $list = array();
        foreach ( $sourceAttributes as $source )
        {
            $kind = self::conversionKind( $source['datatype'], $dest['datatype'], $rules );
            if ( $kind !== null )
                $list[] = array( 'identifier' => $source['identifier'], 'name' => isset( $source['name'] ) ? $source['name'] : $source['identifier'],
                                 'datatype' => $source['datatype'], 'kind' => $kind );
        }
        return $list;
    }

    /**
     * A message: a code, the English text with %placeholders and their values.
     */
    public static function message( $code, $text, array $params = array() )
    {
        return array( 'code' => $code, 'text' => $text, 'params' => $params );
    }

    /**
     * The English text of a message with its values filled in (the command's output, logs).
     *
     * @param array $message
     * @return string
     */
    public static function render( array $message )
    {
        return strtr( $message['text'], array_map( 'strval', $message['params'] ) );
    }

    /**
     * The plan of a class change of one object.
     *
     * @param array $facts see the class
     * @param array $mapping destination identifier => source identifier or '' (missing destinations count as '')
     * @param array $rules rulesFromSettings()
     * @return array ok (no errors), errors, warnings, rows, dropped, stats, mapping (complete and normalized)
     */
    public static function plan( array $facts, array $mapping, array $rules )
    {
        $errors = array();
        $warnings = array();
        $source = $facts['source_class'];
        $dest = $facts['dest_class'];
        $object = $facts['object'];
        $values = isset( $facts['values'] ) ? $facts['values'] : array();

        $sourceByIdentifier = array();
        foreach ( $source['attributes'] as $attribute )
            $sourceByIdentifier[$attribute['identifier']] = $attribute;
        $destByIdentifier = array();
        foreach ( $dest['attributes'] as $attribute )
            $destByIdentifier[$attribute['identifier']] = $attribute;

        // the object and the classes
        if ( (int)$source['id'] === (int)$dest['id'] )
            $errors[] = self::message( 'same_class', 'The object is of the class %class already.', array( '%class' => $dest['name'] ) );
        if ( isset( $object['status'] ) && (int)$object['status'] === self::OBJECT_ARCHIVED )
            $errors[] = self::message( 'in_trash', 'The object is in the trash. Restore it before changing its class.' );
        if ( !empty( $object['is_user'] ) )
            $errors[] = self::message( 'user_object', 'The object is a user account. The class of user accounts cannot be changed here.' );
        if ( !empty( $object['locked'] ) )
            $errors[] = self::message( 'locked', 'The object is locked. Unlock it before changing its class.' );
        if ( isset( $facts['can_edit'] ) && !$facts['can_edit'] )
            $errors[] = self::message( 'no_edit', 'You may not edit this object, so you may not change its class either.' );
        if ( !empty( $facts['job'] ) )
            $errors[] = self::message( 'job_running', 'A background job works on this part of the tree (%job). Wait until it is done.', array( '%job' => $facts['job'] ) );
        if ( empty( $object['languages'] ) || empty( $object['versions'] ) )
            $errors[] = self::message( 'no_versions', 'The object has no versions or no languages to change.' );
        if ( !$dest['attributes'] )
            $errors[] = self::message( 'no_attributes', 'The class %class has no attributes.', array( '%class' => $dest['name'] ) );
        foreach ( $dest['attributes'] as $attribute )
        {
            if ( in_array( $attribute['datatype'], $rules['unsupported'], true ) )
                $errors[] = self::message( 'unsupported', 'The class %class has an attribute of the datatype %datatype (%attribute), which cannot be changed into.',
                                           array( '%class' => $dest['name'], '%datatype' => $attribute['datatype'], '%attribute' => $attribute['name'] ) );
        }
        foreach ( $source['attributes'] as $attribute )
        {
            if ( in_array( $attribute['datatype'], $rules['unsupported'], true ) )
                $errors[] = self::message( 'unsupported_source', 'The attribute %attribute is of the datatype %datatype, which cannot be changed from.',
                                           array( '%datatype' => $attribute['datatype'], '%attribute' => $attribute['name'] ) );
        }

        // the mapping: complete, every name known, every pair convertible
        $normalized = array();
        foreach ( $dest['attributes'] as $attribute )
        {
            $from = isset( $mapping[$attribute['identifier']] ) ? trim( (string)$mapping[$attribute['identifier']] ) : '';
            $normalized[$attribute['identifier']] = $from;
        }
        foreach ( $mapping as $destIdentifier => $from )
        {
            if ( !isset( $destByIdentifier[$destIdentifier] ) )
                $errors[] = self::message( 'unknown_destination', 'The class %class has no attribute %identifier.', array( '%class' => $dest['name'], '%identifier' => $destIdentifier ) );
        }

        $rows = array();
        $mappedSources = array();
        $languages = isset( $object['languages'] ) ? $object['languages'] : array();
        foreach ( $dest['attributes'] as $attribute )
        {
            $from = $normalized[$attribute['identifier']];
            $row = array( 'identifier' => $attribute['identifier'], 'name' => $attribute['name'], 'datatype' => $attribute['datatype'],
                          'required' => !empty( $attribute['required'] ), 'source' => '', 'source_name' => '', 'source_datatype' => '',
                          'kind' => self::EMPTY_ATTRIBUTE, 'converter' => '', 'values' => array() );
            if ( $from !== '' )
            {
                if ( !isset( $sourceByIdentifier[$from] ) )
                {
                    $errors[] = self::message( 'unknown_source', 'The class %class has no attribute %identifier.', array( '%class' => $source['name'], '%identifier' => $from ) );
                    $rows[] = $row;
                    continue;
                }
                $sourceAttribute = $sourceByIdentifier[$from];
                $kind = self::conversionKind( $sourceAttribute['datatype'], $attribute['datatype'], $rules );
                if ( $kind === null )
                {
                    $errors[] = self::message( 'incompatible', '%source (%source_datatype) cannot be carried into %destination (%destination_datatype).',
                                               array( '%source' => $sourceAttribute['name'], '%source_datatype' => $sourceAttribute['datatype'],
                                                      '%destination' => $attribute['name'], '%destination_datatype' => $attribute['datatype'] ) );
                }
                $row['source'] = $from;
                $row['source_name'] = $sourceAttribute['name'];
                $row['source_datatype'] = $sourceAttribute['datatype'];
                $row['kind'] = isset( $mappedSources[$from] ) ? self::COPY : ( $kind === null ? '' : $kind );
                if ( $kind === self::CONVERTER )
                    $row['converter'] = $rules['converters'][$sourceAttribute['datatype']]['callable'];
                foreach ( $languages as $language )
                    $row['values'][$language] = isset( $values[$from][$language] ) ? $values[$from][$language] : array( 'has_content' => false, 'text' => '' );
                if ( isset( $mappedSources[$from] ) )
                    $warnings[] = self::message( 'copied', '%source is carried into %destination as well: a copy of the current version\'s content.',
                                                 array( '%source' => $sourceAttribute['name'], '%destination' => $attribute['name'] ) );
                $mappedSources[$from] = true;
            }
            else if ( $row['required'] )
            {
                $warnings[] = self::message( 'required_empty', '%destination is required and stays empty: the next edit asks for a value before it can be published.',
                                             array( '%destination' => $attribute['name'] ) );
            }
            $rows[] = $row;
        }

        // what is dropped: the source attributes nothing takes over
        $dropped = array();
        foreach ( $source['attributes'] as $attribute )
        {
            if ( isset( $mappedSources[$attribute['identifier']] ) )
                continue;
            $withContent = array();
            foreach ( $languages as $language )
            {
                if ( !empty( $values[$attribute['identifier']][$language]['has_content'] ) )
                    $withContent[] = $language;
            }
            $dropped[] = array( 'identifier' => $attribute['identifier'], 'name' => $attribute['name'], 'datatype' => $attribute['datatype'],
                                'languages' => $withContent,
                                'values' => isset( $values[$attribute['identifier']] ) ? $values[$attribute['identifier']] : array() );
            if ( $withContent )
                $warnings[] = self::message( 'dropped_data', '%source holds content in %languages that the new class does not take over. It is kept in the restore point until that is discarded.',
                                             array( '%source' => $attribute['name'], '%languages' => implode( ', ', $withContent ) ) );
        }

        // what an editor should know
        if ( !empty( $source['is_container'] ) && empty( $dest['is_container'] ) && !empty( $object['children_count'] ) )
            $warnings[] = self::message( 'not_container', 'The object has %count sub-items, but %class is no container: they stay, but no new ones can be added below it.',
                                         array( '%count' => (int)$object['children_count'], '%class' => $dest['name'] ) );
        $drafts = 0;
        $othersDrafts = 0;
        foreach ( isset( $object['versions'] ) ? $object['versions'] : array() as $version )
        {
            if ( in_array( (int)$version['status'], array( self::VERSION_DRAFT, self::VERSION_PENDING, self::VERSION_INTERNAL_DRAFT ), true ) )
            {
                $drafts++;
                if ( isset( $object['current_user_id'] ) && (int)$version['creator_id'] !== (int)$object['current_user_id'] )
                    $othersDrafts++;
            }
        }
        if ( $othersDrafts )
            $warnings[] = self::message( 'drafts_of_others', '%count drafts of other users are changed too. Tell them before they publish an edit that was open during the change.',
                                         array( '%count' => $othersDrafts ) );
        else if ( $drafts )
            $warnings[] = self::message( 'drafts', '%count drafts are changed too.', array( '%count' => $drafts ) );
        if ( !empty( $facts['reverse_relations'] ) )
            $warnings[] = self::message( 'reverse_relations', '%count objects relate to this one through an attribute. A relation attribute that only accepts certain classes may no longer accept it.',
                                         array( '%count' => (int)$facts['reverse_relations'] ) );

        $versions = isset( $object['versions'] ) ? count( $object['versions'] ) : 0;
        $stats = array( 'versions' => $versions, 'languages' => count( $languages ), 'drafts' => $drafts,
                        'attributes' => count( $rows ), 'carried' => count( $mappedSources ), 'dropped' => count( $dropped ),
                        'rows_changed' => $versions * count( $languages ) * count( $rows ) );

        return array( 'ok' => !$errors, 'errors' => $errors, 'warnings' => $warnings, 'rows' => $rows, 'dropped' => $dropped,
                      'stats' => $stats, 'mapping' => $normalized );
    }

    /**
     * The token of a plan: what was previewed is what is applied. It changes when the object gets a new version, is
     * modified, or the classes or the mapping differ.
     *
     * @param array $parts object_id, modified, current_version, source_class_id, dest_class_id, mapping
     * @return string
     */
    public static function token( array $parts )
    {
        $mapping = isset( $parts['mapping'] ) ? (array)$parts['mapping'] : array();
        ksort( $mapping );
        return substr( sha1( json_encode( array( (int)$parts['object_id'], (int)$parts['modified'], (int)$parts['current_version'],
                                                 (int)$parts['source_class_id'], (int)$parts['dest_class_id'], $mapping ) ) ), 0, 20 );
    }
}
