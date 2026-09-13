<?php
//
// SOFTWARE NAME: expChangeClass
// SOFTWARE LICENSE: GNU General Public License v2.0
//
// Datatype converters, gathered into one class.
//
// Upstream shipped three files, each with one converter class whose methods
// were declared non-static and then invoked through
// call_user_func_array( array( $class, $method ) ) - a fatal on PHP 8. They
// are static here.
//
// changeclass.ini keys a converter on the SOURCE datatype, so a source with
// several possible destinations can only name one script. Upstream worked
// around that by declaring [ezstring] twice, which does not work: the second
// group wins and ezstring -> ezxmltext was silently unreachable. One class with
// one entry point per source datatype, dispatching on the destination, removes
// the need for a duplicate group.
//

class expChangeClassConverters
{
    /**
     * ezstring or eztext to ezxmltext, ezinteger or ezfloat.
     */
    static function convertString( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        switch ( $destObjectAttr->attribute( 'data_type_string' ) )
        {
            case 'ezxmltext':
                return self::convertToXml( $newObjectAttr, $sourceObjectAttr, $destObjectAttr );

            case 'ezinteger':
            case 'ezfloat':
                return self::convertToNumber( $newObjectAttr, $sourceObjectAttr, $destObjectAttr );
        }

        return false;
    }

    /**
     * Wrap plain text as an XML block.
     *
     * The text is escaped before it is interpolated. Upstream built the markup
     * by substituting the raw value straight into the string, so any content
     * holding an ampersand or an angle bracket - "Tom & Jerry", "a < b", or a
     * pasted fragment of HTML - produced XML that the parser either rejected or
     * silently reinterpreted, losing part of the field.
     */
    static function convertToXml( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        $text = (string) $sourceObjectAttr->attribute( 'data_text' );

        if ( trim( $text ) === '' )
        {
            $newObjectAttr->setAttribute( 'data_text', '' );
            return true;
        }

        $escaped = htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );

        $parser = new eZSimplifiedXMLInputParser( $newObjectAttr->attribute( 'contentobject_id' ) );
        $parser->setParseLineBreaks( true );
        $document = $parser->process( "<section><paragraph>$escaped</paragraph></section>" );

        // process() hands back an array of messages rather than a document when
        // the input will not parse. Storing that would blank the field.
        if ( !$document instanceof DOMDocument )
        {
            eZDebug::writeError( 'Could not parse text into xml for attribute ' .
                                 $newObjectAttr->attribute( 'contentclassattribute_id' ),
                                 __METHOD__ );
            return false;
        }

        $newObjectAttr->setAttribute( 'data_text', eZXMLTextType::domString( $document ) );
        return true;
    }

    /**
     * Text to ezinteger or ezfloat.
     */
    static function convertToNumber( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        $text = trim( (string) $sourceObjectAttr->attribute( 'data_text' ) );

        switch ( $destObjectAttr->attribute( 'data_type_string' ) )
        {
            case 'ezfloat':
                // A comma is a decimal separator in most of the locales this
                // content comes from; PHP only reads a dot.
                $newObjectAttr->setAttribute( 'data_float', (float) str_replace( ',', '.', $text ) );
                return true;

            case 'ezinteger':
                $newObjectAttr->setAttribute( 'data_int', (int) $text );
                return true;
        }

        return false;
    }

    /**
     * ezobjectrelationlist to ezobjectrelationlistbloc.
     *
     * Rewritten onto DOMDocument. Upstream used eZXML::domTree() and
     * get_elements_by_tagname(), the PHP 4 xml wrapper that the kernel dropped
     * long ago, so this converter could only ever fatal.
     */
    static function convertRelationListToBloc( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        $xmlString = (string) $sourceObjectAttr->attribute( 'data_text' );

        if ( trim( $xmlString ) === '' )
        {
            $newObjectAttr->setAttribute( 'data_text', '' );
            return true;
        }

        $document = new DOMDocument( '1.0', 'utf-8' );

        $previous = libxml_use_internal_errors( true );
        $loaded = $document->loadXML( $xmlString );
        libxml_clear_errors();
        libxml_use_internal_errors( $previous );

        if ( !$loaded )
        {
            eZDebug::writeError( 'Could not parse relation list xml for attribute ' .
                                 $newObjectAttr->attribute( 'contentclassattribute_id' ),
                                 __METHOD__ );
            return false;
        }

        foreach ( $document->getElementsByTagName( 'relation-item' ) as $item )
        {
            foreach ( array( 'dateDebDay', 'dateDebMonth', 'dateDebYear', 'dateDebHour' ) as $name )
                $item->setAttribute( $name, '' );
        }

        $newObjectAttr->setAttribute( 'data_text', $document->saveXML() );
        return true;
    }
}

?>
