# Using expchangeclass

The pages of the administration are described in [Change class](index.md): one object with its preview and restore
point, the overview, the bulk change as a background job, and the policies for editors. This page covers the parameter
files of the older batch script, the datatype converters and calling the conversion from code.

## Converting every object of a class

`./console exp:changeclass --from=<class> --class=<class> [--subtree=<node>] --apply` does it with a preview and a
restore point per object ([Bulk changes and the command line](bulk-and-command-line.md)). The older script still
works, and goes through the same plan and restore point:

```
php extension/expchangeclass/scripts/classconvert.php -s <siteaccess> --param-file=<file>
```

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

The views are gated on the policies of [Policies for editors](policies.md).
