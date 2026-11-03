<?php
//
// Created on: <15-Jun-2007>
//
// SOFTWARE NAME: expChangeClass
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2007 Bartek Modzelewski
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

/**
 * Raised when a conversion cannot be completed.
 *
 * The predecessors printed a message and called die() from inside the attribute
 * loop, which ran after the transaction had been opened and after some
 * attributes had already been re-pointed at the destination class. The request
 * ended with the object half converted. Everything that used to die() now
 * throws, so convertObject() can roll back and leave the content as it was.
 */
class expChangeClassException extends Exception
{
}

class conversionFunctions
{
    /**
     * Cached parse of [SimpleConversion]/SupportedConversion.
     *
     * Static because every entry point into this class is a static call:
     * module code calls conversionFunctions::convertObject(), and the template
     * fetch in function_definition.php reaches fetchSimpleConversionArray()
     * through call_user_func_array( array( $class, $method ) ). Calling a
     * non-static method that way is a fatal error on PHP 8, which is what the
     * upstream versions did.
     */
    static $simpleConversionArray = false;

    static function getSimpleConversionArray()
    {
        if ( self::$simpleConversionArray !== false )
            return self::$simpleConversionArray;

        $ini = eZINI::instance( 'changeclass.ini' );
        if ( !$ini->hasGroup( 'SimpleConversion' ) )
            return false;

        $simpleConversion = $ini->variable( 'SimpleConversion', 'SupportedConversion' );
        if ( empty( $simpleConversion ) )
            return false;

        $simpleConversionArray = array();
        foreach ( $simpleConversion as $item )
        {
            $element = explode( ';', $item );
            // A malformed line has no destination half. Skipping it beats
            // indexing $element[1] and storing a null destination that later
            // compares equal to nothing.
            if ( count( $element ) < 2 )
                continue;

            $source = trim( $element[0] );
            $destination = trim( $element[1] );
            if ( $source === '' || $destination === '' )
                continue;

            if ( !isset( $simpleConversionArray[$source] ) )
                $simpleConversionArray[$source] = array();

            $simpleConversionArray[$source][] = $destination;
        }

        self::$simpleConversionArray = $simpleConversionArray;
        return $simpleConversionArray;
    }

    static function fetchSimpleConversionArray()
    {
        return array( 'result' => self::getSimpleConversionArray() );
    }

    /**
     * True when the datatype pair is listed in [SimpleConversion].
     */
    static function isSimpleConversion( $sourceDataType, $destDataType )
    {
        $map = self::getSimpleConversionArray();
        return is_array( $map )
            && isset( $map[$sourceDataType] )
            && in_array( $destDataType, $map[$sourceDataType], true );
    }

    /**
     * Run the configured converter for a datatype change.
     *
     * Returns silently when the datatypes match or the pair is a simple
     * conversion. Throws when a conversion is needed and cannot be performed,
     * so the caller can roll back rather than leave the object half converted.
     */
    static function customConverter( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        $sourceDataType = $sourceObjectAttr->attribute( 'data_type_string' );
        $destDataType = $destObjectAttr->attribute( 'data_type_string' );

        if ( $sourceDataType === $destDataType )
            return;

        if ( self::isSimpleConversion( $sourceDataType, $destDataType ) )
            return;

        $ini = eZINI::instance( 'changeclass.ini' );

        if ( !$ini->hasGroup( $sourceDataType ) )
        {
            throw new expChangeClassException(
                "No conversion is configured from '$sourceDataType' to '$destDataType'." );
        }

        $possibleDestinations = $ini->variable( $sourceDataType, 'SupportedDestination' );
        if ( !is_array( $possibleDestinations ) )
            $possibleDestinations = array( $possibleDestinations );

        if ( !in_array( $destDataType, $possibleDestinations, true ) )
        {
            throw new expChangeClassException(
                "Conversion from '$sourceDataType' to '$destDataType' is not supported." );
        }

        $script = $ini->hasVariable( $sourceDataType, 'Script' )
                ? $ini->variable( $sourceDataType, 'Script' ) : '';
        if ( $script === '' || !file_exists( $script ) )
        {
            throw new expChangeClassException(
                "Converter script for '$sourceDataType' not found: '$script'." );
        }

        include_once( $script );

        $class = $ini->hasVariable( $sourceDataType, 'Class' )
               ? trim( $ini->variable( $sourceDataType, 'Class' ) ) : '';
        $function = $ini->hasVariable( $sourceDataType, 'Function' )
                  ? trim( $ini->variable( $sourceDataType, 'Function' ) ) : '';

        if ( $function === '' )
        {
            throw new expChangeClassException(
                "No converter function configured for '$sourceDataType'." );
        }

        $callback = ( $class !== '' ) ? array( $class, $function ) : $function;

        if ( !is_callable( $callback ) )
        {
            $shown = ( $class !== '' ) ? "$class::$function" : $function;
            throw new expChangeClassException(
                "Converter '$shown' for '$sourceDataType' is not callable." );
        }

        $result = call_user_func_array( $callback,
                                        array( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr ) );

        if ( !$result )
        {
            $attributeID = $newObjectAttr->attribute( 'contentclassattribute_id' );
            throw new expChangeClassException(
                "Converter '$function' failed on class attribute $attributeID" .
                " ('$sourceDataType' to '$destDataType')." );
        }
    }

    /**
     * Convert one object to another content class.
     *
     * Works on the object's attribute rows as they are stored, every version and every language a row exists for:
     * the predecessors walked the languages of the siteaccess (eZContentObject::languages() keeps only the
     * prioritized ones) and the data map of the current version, so a translation in a language the siteaccess does
     * not list, or a language only an older version has, kept attributes of the old class on an object of the new.
     *
     *  - a carried attribute row (the first destination that names its source) is re-pointed at the new class
     *    attribute, converted when the datatypes differ, and stored;
     *  - a source named by more than one destination is copied into the further ones, from the row of the same
     *    version and language (data, sort keys and content);
     *  - a destination with no source gets an initialized empty row per version and language;
     *  - a source row nothing carries is removed as a row only: its stored data (files, image aliases, the
     *    datatype's own tables) stays for the restore point (expChangeClassRestorePoint), which puts the row back,
     *    and goes when the restore point is discarded;
     *  - the relation rows of relation attributes follow their attribute, those of a dropped one go.
     *
     * Everything, the object's contentclass_id included, runs in one transaction and is rolled back as a unit on any
     * error. Returns true on success; on failure the content is unchanged and the reason is in $errorMessage.
     */
    static function convertObject( $sourceObjectID, $destinationClassID, $mapping, &$errorMessage = null )
    {
        $errorMessage = null;
        $sourceObjectID = (int) $sourceObjectID;

        $sourceObject = eZContentObject::fetch( $sourceObjectID );
        if ( !$sourceObject instanceof eZContentObject )
        {
            $errorMessage = "Source object $sourceObjectID not found.";
            return false;
        }

        $destClass = is_numeric( $destinationClassID ) ? eZContentClass::fetch( (int) $destinationClassID )
                                                       : eZContentClass::fetchByIdentifier( $destinationClassID );
        if ( !$destClass instanceof eZContentClass )
        {
            $errorMessage = "Destination class '$destinationClassID' not found.";
            return false;
        }
        $destinationClassID = (int) $destClass->attribute( 'id' );
        $destClassDataMap = $destClass->dataMap();
        if ( !$destClassDataMap )
        {
            $errorMessage = "Destination class '$destinationClassID' has no attributes.";
            return false;
        }

        $sourceClass = eZContentClass::fetch( (int) $sourceObject->attribute( 'contentclass_id' ) );
        if ( !$sourceClass instanceof eZContentClass )
        {
            $errorMessage = "Source class " . $sourceObject->attribute( 'contentclass_id' ) . " not found.";
            return false;
        }
        $sourceClassDataMap = $sourceClass->dataMap();
        if ( !is_array( $sourceClassDataMap ) )
            $sourceClassDataMap = array();
        if ( (int) $sourceClass->attribute( 'id' ) === $destinationClassID )
        {
            $errorMessage = 'Source and destination class are the same; nothing to convert.';
            return false;
        }

        // The whole mapping is validated before anything is written.
        $mapping = is_array( $mapping ) ? $mapping : array();
        foreach ( $mapping as $destIdentifier => $sourceIdentifier )
        {
            if ( !isset( $destClassDataMap[$destIdentifier] ) || !$destClassDataMap[$destIdentifier] instanceof eZContentClassAttribute )
            {
                $errorMessage = "Destination class has no attribute '$destIdentifier'.";
                return false;
            }
            if ( $sourceIdentifier !== '' && $sourceIdentifier !== null && !isset( $sourceClassDataMap[$sourceIdentifier] ) )
            {
                $errorMessage = "Source class has no attribute '$sourceIdentifier'.";
                return false;
            }
        }
        // a destination the mapping does not name stays empty
        foreach ( $destClassDataMap as $destIdentifier => $destClassAttribute )
            if ( !array_key_exists( $destIdentifier, $mapping ) )
                $mapping[$destIdentifier] = '';

        // which destination carries a source (the first one naming it), which ones copy it, which stay empty
        $carries = array();
        $copies = array();
        $empty = array();
        foreach ( $mapping as $destIdentifier => $sourceIdentifier )
        {
            if ( $sourceIdentifier === '' || $sourceIdentifier === null )
                $empty[] = $destIdentifier;
            else if ( !isset( $carries[$sourceIdentifier] ) )
                $carries[$sourceIdentifier] = $destIdentifier;
            else
                $copies[$destIdentifier] = $sourceIdentifier;
        }
        $identifierOfClassAttribute = array();
        foreach ( $sourceClassDataMap as $identifier => $classAttribute )
            $identifierOfClassAttribute[(int) $classAttribute->attribute( 'id' )] = $identifier;

        global $sourceClassName, $destClassName, $sourceClassIdentifier, $destClassIdentifier, $sourceObjectCount;
        $sourceClassName       = $sourceObject->className();
        $destClassName         = $destClass->attribute( 'name' );
        $sourceClassIdentifier = $sourceObject->contentClassIdentifier();
        $destClassIdentifier   = $destClass->attribute( 'identifier' );
        $sourceObjectCount     = $sourceClass->objectCount() - 1;

        $db = eZDB::instance();
        $rows = $db->arrayQuery( "SELECT id, version, language_code, contentclassattribute_id FROM ezcontentobject_attribute
                                  WHERE contentobject_id = $sourceObjectID ORDER BY version, language_code, id" );
        if ( !$rows )
        {
            $errorMessage = "Object $sourceObjectID has no attributes to convert.";
            return false;
        }

        $db->begin();
        try
        {
            // the (version, language) pairs the object has rows for, and the source row of each pair and attribute
            $pairs = array();
            $originals = array();
            foreach ( $rows as $row )
            {
                $pair = (int) $row['version'] . '/' . $row['language_code'];
                $pairs[$pair] = array( (int) $row['version'], (string) $row['language_code'] );
                $classAttributeID = (int) $row['contentclassattribute_id'];
                $identifier = isset( $identifierOfClassAttribute[$classAttributeID] ) ? $identifierOfClassAttribute[$classAttributeID] : null;
                $attribute = eZContentObjectAttribute::fetch( (int) $row['id'], (int) $row['version'] );
                if ( !$attribute instanceof eZContentObjectAttribute )
                    continue;
                // the row as it was stored: the converters and the copies read the source datatype and data from it
                if ( $identifier !== null )
                    $originals[$identifier][$pair] = clone $attribute;
                if ( $identifier !== null && isset( $carries[$identifier] ) )
                {
                    $destClassAttribute = $destClassDataMap[$carries[$identifier]];
                    $attribute->setAttribute( 'contentclassattribute_id', (int) $destClassAttribute->attribute( 'id' ) );
                    self::customConverter( $attribute, $originals[$identifier][$pair], $destClassAttribute );
                    $attribute->store();
                }
                else
                {
                    // expChangeClassKeepDropped: only the row goes; its stored data stays for the restore point
                    eZPersistentObject::removeObject( eZContentObjectAttribute::definition(),
                                                      array( 'id' => (int) $row['id'], 'version' => (int) $row['version'] ) );
                }
            }

            // further destinations of one source: a copy of the same version's and language's row
            foreach ( $copies as $destIdentifier => $sourceIdentifier )
            {
                $destClassAttribute = $destClassDataMap[$destIdentifier];
                $idOfLanguage = array();
                foreach ( $pairs as $pair => $versionLanguage )
                {
                    $copy = eZContentObjectAttribute::create( (int) $destClassAttribute->attribute( 'id' ), $sourceObjectID, $versionLanguage[0], $versionLanguage[1] );
                    // one id per language across the versions, as the kernel keeps it
                    if ( isset( $idOfLanguage[$versionLanguage[1]] ) )
                        $copy->setAttribute( 'id', $idOfLanguage[$versionLanguage[1]] );
                    if ( isset( $originals[$sourceIdentifier][$pair] ) )
                    {
                        $original = $originals[$sourceIdentifier][$pair];
                        foreach ( array( 'data_text', 'data_int', 'data_float', 'sort_key_int', 'sort_key_string' ) as $field )
                            $copy->setAttribute( $field, $original->attribute( $field ) );
                        $copy->setContent( $original->content() );
                        self::customConverter( $copy, $original, $destClassAttribute );
                    }
                    $copy->store();
                    $idOfLanguage[$versionLanguage[1]] = (int) $copy->attribute( 'id' );
                }
            }

            // destinations with no source: an initialized row per version and language
            foreach ( $empty as $destIdentifier )
            {
                $destClassAttribute = $destClassDataMap[$destIdentifier];
                $idOfLanguage = array();
                foreach ( $pairs as $versionLanguage )
                {
                    $newAttribute = eZContentObjectAttribute::create( (int) $destClassAttribute->attribute( 'id' ), $sourceObjectID, $versionLanguage[0], $versionLanguage[1] );
                    if ( isset( $idOfLanguage[$versionLanguage[1]] ) )
                        $newAttribute->setAttribute( 'id', $idOfLanguage[$versionLanguage[1]] );
                    $newAttribute->initialize();
                    $newAttribute->store();
                    $idOfLanguage[$versionLanguage[1]] = (int) $newAttribute->attribute( 'id' );
                }
            }

            // The relation rows of relation attributes follow their attribute: those of a carried attribute are
            // re-pointed at the new class attribute, those of a dropped one go (the restore point keeps them).
            foreach ( $sourceClassDataMap as $identifier => $classAttribute )
            {
                $oldID = (int) $classAttribute->attribute( 'id' );
                if ( isset( $carries[$identifier] ) )
                    $db->query( 'UPDATE ezcontentobject_link SET contentclassattribute_id = ' . (int) $destClassDataMap[$carries[$identifier]]->attribute( 'id' ) .
                                " WHERE from_contentobject_id = $sourceObjectID AND contentclassattribute_id = $oldID" );
                else
                    $db->query( "DELETE FROM ezcontentobject_link WHERE from_contentobject_id = $sourceObjectID AND contentclassattribute_id = $oldID" );
            }

            // Inside the transaction: if this fails the attribute moves above are rolled back with it.
            $sourceObject->setAttribute( 'contentclass_id', $destinationClassID );
            $sourceObject->store();

            if ( !$db->commit() )
                throw new expChangeClassException( 'The transaction could not be committed.' );
        }
        catch ( Exception $e )
        {
            $db->rollback();
            $errorMessage = $e->getMessage();
            eZDebug::writeError( "Conversion of object $sourceObjectID to class $destinationClassID rolled back: " . $e->getMessage(), __METHOD__ );
            return false;
        }

        eZContentObject::clearCache( array( $sourceObjectID ) );
        eZContentCacheManager::clearContentCache( $sourceObjectID );

        return true;
    }
}

?>
