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
     * Everything that touches content runs inside a single transaction and is
     * rolled back as a unit. The predecessors opened a transaction, mutated
     * attributes across every version and language, and then either committed
     * or died part way through with no rollback - and changed the object's
     * contentclass_id after the commit, so a failure there left attributes
     * belonging to the new class on an object still claiming the old one.
     *
     * Returns true on success. On failure the content is unchanged and the
     * reason is in $errorMessage.
     */
    static function convertObject( $sourceObjectID, $destinationClassID, $mapping, &$errorMessage = null )
    {
        $errorMessage = null;

        $sourceObject = eZContentObject::fetch( $sourceObjectID );
        if ( !$sourceObject instanceof eZContentObject )
        {
            $errorMessage = "Source object $sourceObjectID not found.";
            return false;
        }

        // Resolve the destination class before dereferencing it. Upstream read
        // $destClass->attribute( 'id' ) immediately after fetchByIdentifier()
        // and only tested for null afterwards, so an unknown identifier was a
        // fatal rather than an error message.
        if ( is_numeric( $destinationClassID ) )
        {
            $destClass = eZContentClass::fetch( (int) $destinationClassID );
        }
        else
        {
            $destClass = eZContentClass::fetchByIdentifier( $destinationClassID );
        }

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

        $sourceClass = eZContentClass::fetchByIdentifier( $sourceObject->attribute( 'class_identifier' ) );
        if ( !$sourceClass instanceof eZContentClass )
        {
            $errorMessage = "Source class '" . $sourceObject->attribute( 'class_identifier' ) . "' not found.";
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

        // Validate the whole mapping before writing anything. A mapping that
        // names an attribute neither class has used to surface as a fatal in
        // the middle of the rewrite, with earlier attributes already stored.
        $mapping = is_array( $mapping ) ? $mapping : array();
        foreach ( $mapping as $destIdentifier => $sourceIdentifier )
        {
            if ( !isset( $destClassDataMap[$destIdentifier] ) ||
                 !$destClassDataMap[$destIdentifier] instanceof eZContentClassAttribute )
            {
                $errorMessage = "Destination class has no attribute '$destIdentifier'.";
                return false;
            }

            if ( $sourceIdentifier !== '' && $sourceIdentifier !== null &&
                 !isset( $sourceClassDataMap[$sourceIdentifier] ) )
            {
                $errorMessage = "Source class has no attribute '$sourceIdentifier'.";
                return false;
            }
        }

        $versions = $sourceObject->attribute( 'versions' );
        $objectVersions = array();
        if ( is_array( $versions ) )
        {
            foreach ( $versions as $version )
                $objectVersions[] = $version->attribute( 'version' );
        }

        $languages = $sourceObject->languages();
        $languageArray = array();
        if ( is_array( $languages ) )
        {
            foreach ( $languages as $locale => $language )
                $languageArray[] = $locale;
        }

        if ( !$objectVersions || !$languageArray )
        {
            $errorMessage = "Object $sourceObjectID has no versions or no languages to convert.";
            return false;
        }

        global $sourceClassName, $destClassName, $sourceClassIdentifier, $destClassIdentifier, $sourceObjectCount;
        $sourceClassName       = $sourceObject->className();
        $destClassName         = $destClass->attribute( 'name' );
        $sourceClassIdentifier = $sourceObject->contentClassIdentifier();
        $destClassIdentifier   = $destClass->attribute( 'identifier' );
        $sourceObjectCount     = $sourceClass->objectCount() - 1;

        $db = eZDB::instance();
        $db->begin();

        try
        {
            foreach ( $languageArray as $languageCode )
            {
                $usedAttributes = array();
                $missingAttributes = array();
                $duplicatedAttributes = array();
                $sourceObjectDataMap = $sourceObject->fetchDataMap( false, $languageCode );
                if ( !is_array( $sourceObjectDataMap ) )
                    $sourceObjectDataMap = array();

                foreach ( $mapping as $destIdentifier => $sourceIdentifier )
                {
                    if ( $sourceIdentifier === '' || $sourceIdentifier === null )
                    {
                        $missingAttributes[] = $destIdentifier;
                        continue;
                    }

                    // One source attribute may feed several destination ones;
                    // the extra copies are created further down.
                    if ( in_array( $sourceIdentifier, $usedAttributes, true ) )
                    {
                        $duplicatedAttributes[$destIdentifier] = $sourceIdentifier;
                        continue;
                    }

                    $usedAttributes[] = $sourceIdentifier;

                    // The source object may simply not carry this attribute in
                    // this language; that is not an error.
                    if ( !isset( $sourceObjectDataMap[$sourceIdentifier] ) ||
                         !$sourceObjectDataMap[$sourceIdentifier] instanceof eZContentObjectAttribute )
                    {
                        continue;
                    }

                    foreach ( $objectVersions as $version )
                    {
                        $sourceObjectAttr = eZContentObjectAttribute::fetch(
                            $sourceObjectDataMap[$sourceIdentifier]->attribute( 'id' ),
                            $version );

                        if ( !$sourceObjectAttr instanceof eZContentObjectAttribute )
                            continue;

                        $sourceObjectAttr->setAttribute(
                            'contentclassattribute_id',
                            $destClassDataMap[$destIdentifier]->attribute( 'id' ) );

                        self::customConverter(
                            $sourceObjectAttr,
                            $sourceObjectDataMap[$sourceIdentifier],
                            $destClassDataMap[$destIdentifier] );

                        $sourceObjectAttr->store();
                    }
                }

                // Source attributes mapped onto more than one destination.
                foreach ( $duplicatedAttributes as $destIdentifier => $sourceIdentifier )
                {
                    if ( !isset( $sourceObjectDataMap[$sourceIdentifier] ) ||
                         !$sourceObjectDataMap[$sourceIdentifier] instanceof eZContentObjectAttribute )
                    {
                        continue;
                    }

                    $attributeID = $destClassDataMap[$destIdentifier]->attribute( 'id' );
                    $newAttribute = false;

                    foreach ( $objectVersions as $index => $version )
                    {
                        if ( $index === 0 || !$newAttribute instanceof eZContentObjectAttribute )
                        {
                            $newAttribute = eZContentObjectAttribute::create(
                                $attributeID, $sourceObjectID, $version, $languageCode );
                            $newAttribute->setContent( $sourceObjectDataMap[$sourceIdentifier]->content() );
                            self::customConverter( $newAttribute,
                                                   $sourceObjectDataMap[$sourceIdentifier],
                                                   $destClassDataMap[$destIdentifier] );
                            $newAttribute->store();
                        }
                        else
                        {
                            $clonedAttribute = $newAttribute->cloneContentObjectAttribute(
                                $version, $objectVersions[0], $sourceObjectID );
                            if ( !$clonedAttribute instanceof eZContentObjectAttribute )
                                continue;
                            $clonedAttribute->setContent( $sourceObjectDataMap[$sourceIdentifier]->content() );
                            self::customConverter( $clonedAttribute,
                                                   $sourceObjectDataMap[$sourceIdentifier],
                                                   $destClassDataMap[$destIdentifier] );
                            $clonedAttribute->sync();
                        }
                    }
                }

                // Destination attributes with no source: create them empty.
                foreach ( $missingAttributes as $destIdentifier )
                {
                    $attributeID = $destClassDataMap[$destIdentifier]->attribute( 'id' );
                    $newAttribute = false;

                    foreach ( $objectVersions as $index => $version )
                    {
                        if ( $index === 0 || !$newAttribute instanceof eZContentObjectAttribute )
                        {
                            $newAttribute = eZContentObjectAttribute::create(
                                $attributeID, $sourceObjectID, $version, $languageCode );
                            $newAttribute->store();
                        }
                        else
                        {
                            $clonedAttribute = $newAttribute->cloneContentObjectAttribute(
                                $version, $objectVersions[0], $sourceObjectID );
                            if ( $clonedAttribute instanceof eZContentObjectAttribute )
                                $clonedAttribute->sync();
                        }
                    }
                }

                // Source attributes the mapping did not carry over.
                foreach ( array_keys( $sourceClassDataMap ) as $oldIdentifier )
                {
                    if ( in_array( $oldIdentifier, $usedAttributes, true ) )
                        continue;

                    if ( !isset( $sourceObjectDataMap[$oldIdentifier] ) ||
                         !$sourceObjectDataMap[$oldIdentifier] instanceof eZContentObjectAttribute )
                    {
                        continue;
                    }

                    $attributeID = $sourceObjectDataMap[$oldIdentifier]->attribute( 'id' );
                    foreach ( $objectVersions as $version )
                    {
                        $oldAttribute = eZContentObjectAttribute::fetch( $attributeID, $version );
                        if ( $oldAttribute instanceof eZContentObjectAttribute )
                            $oldAttribute->removeThis( $attributeID );
                    }
                }
            }

            // Inside the transaction: if this fails the attribute moves above
            // are rolled back with it, instead of leaving attributes of the new
            // class on an object still declaring the old one.
            $sourceObject->setAttribute( 'contentclass_id', $destinationClassID );
            $sourceObject->store();

            $db->commit();
        }
        catch ( Exception $e )
        {
            $db->rollback();
            $errorMessage = $e->getMessage();
            eZDebug::writeError( "Conversion of object $sourceObjectID to class" .
                                 " $destinationClassID rolled back: " . $e->getMessage(),
                                 __METHOD__ );
            return false;
        }

        eZContentCacheManager::clearContentCache( $sourceObjectID );

        return true;
    }
}

?>
