# Using expchangeclass

Conversion rewrites published content in place, across every version and every
language of the object. **Take a database backup before you start**, and convert
one object before you convert a class.

## Converting one object

1. In the administration interface, browse to the object.
2. Open the context menu on the node and choose **Change class**.
3. Pick the destination class.
4. Map the attributes, then confirm.

The mapping form lists every attribute of the destination class with a dropdown
of the source attributes. Each row pre-selects the source attribute whose
identifier matches the destination; change it where the identifiers differ.

Leave a row empty to create that destination attribute with no content. Any
source attribute you do not map is **removed** from the object.

The same source attribute may be mapped onto several destination attributes; the
extra copies are created from its content.

The result page states whether the content changed. If it did not, it says why
and nothing was written.

## Converting every object of a class

The confirmation page writes a parameter file into the cache directory and shows
the command to run. It looks like this:

```
php extension/expchangeclass/scripts/classconvert.php -s <siteaccess> --param-file=<file>
```

Use the siteaccess you were just working in, so the script uses the same
database and cache directory.

### Options

| option | meaning |
| --- | --- |
| `--param-file=` | the parameter file written by the confirmation page |
| `--sub-tree=` | restrict to a subtree, by node id |
| `--db-host=`, `--db-user=`, `--db-password=`, `--db-database=`, `--db-type=` | override the siteaccess database |

### The parameter file

Line 1 is the class pair, every later line is an attribute pair, source first:

```
old_class_identifier:new_class_identifier
old_attribute_identifier:new_attribute_identifier
another_old_attribute:another_new_attribute
```

Blank and malformed lines are reported and skipped. The run refuses to start if
the class line is incomplete or names the same class twice.

### What it reports

Each converted object prints a `+`, and a progress line gives the running count.
Objects that fail are named with the reason and left untouched — the run
continues past them. If a whole batch fails, the run stops rather than retrying
the same objects.

With `ScriptLog=enabled` in `changeclass.ini` (the default) every outcome is
appended to `logExpChangeClass.txt` in the cache directory.

## Datatype conversion

An attribute mapped onto one of the same datatype is moved across unchanged.

Where the datatypes differ, one of two things has to be configured in
`changeclass.ini`, otherwise the conversion is refused before anything is
written.

### Simple conversions

Pairs that need no transformation, listed as `source;destination`:

```
[SimpleConversion]
SupportedConversion[]=eztext;ezstring
SupportedConversion[]=ezstring;eztext
SupportedConversion[]=ezemail;eztext
SupportedConversion[]=ezemail;ezstring
```

### Converted datatypes

Shipped out of the box:

| from | to |
| --- | --- |
| `ezstring`, `eztext` | `ezxmltext` |
| `ezstring`, `eztext` | `ezinteger`, `ezfloat` |
| `ezobjectrelationlist` | `ezobjectrelationlistbloc` |

`ezuser` is listed in `UnsupportedDataTypeArray` and is never converted.

### Adding your own

A group is keyed on the **source** datatype and carries one script, so a source
with several destinations needs one entry point that dispatches on the
destination:

```
[mydatatype]
SupportedDestination[]
SupportedDestination[]=ezstring
SupportedDestination[]=ezxmltext
Script=extension/myextension/classes/converters.php
Class=myConverters
Function=convert
```

Do not declare the same group twice. A repeated group does not merge — the
second occurrence wins and the destinations in the first become unreachable.
That is how `ezstring` to `ezxmltext` came to be silently unavailable upstream.

Your method is called statically and must be declared `static`:

```php
class myConverters
{
    static function convert( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        $newObjectAttr->setAttribute( 'data_text', ... );
        return true;   // false aborts the conversion and rolls it back
    }
}
```

`$newObjectAttr` is passed by reference; the other two are the source and
destination attributes, for reading. Returning anything falsy aborts the whole
conversion and rolls it back — the object is left as it was.

Escape anything you interpolate into markup. The shipped text-to-XML converter
passes the field through `htmlspecialchars()` first, because a value containing
`&` or `<` otherwise produces XML that is rejected or silently reinterpreted.

## Calling it from your own code

```php
$error = null;
$ok = conversionFunctions::convertObject( $objectID, $destClassIdentifier, $mapping, $error );
if ( !$ok )
{
    eZDebug::writeError( $error, __METHOD__ );
    // nothing was written; the content is unchanged
}
```

`$mapping` is keyed by **destination** attribute identifier, with the source
attribute identifier as the value. An empty value creates the destination
attribute empty.

## Permissions

The three views are gated on `changeclass/convert`. Grant that policy to the
roles allowed to convert content; it is not implied by general content edit
access.
