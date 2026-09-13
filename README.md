# expchangeclass

Change an existing content object's content class, mapping each attribute of the
old class onto one of the new. Works on one object from the administration
interface, or on every object of a class from the command line.

For Exponential Legacy / Exponential 6 on PHP 8.

## Why this fork exists

This is a fork of [eZChangeclass](https://github.com/dbteam/ezchangeclass) by
Bartek Modzelewski. Two forks of it were in circulation and neither was safe to
run here.

The Netgen fork keeps the original PHP 4 shape: `conversionFunctions` declares a
`conversionFunctions()` constructor and non-static `customConverter()` and
`convertObject()`, while the module calls them as
`conversionFunctions::convertObject(...)`. PHP removed PHP 4 constructors in
version 8 and made a static call to a non-static method fatal, so that fork
stops at the line that does the work:

```
Error: Non-static method conversionFunctions::customConverter()
       cannot be called statically
```

The OpenContent fork fixed that and added guards, but carried its own faults: a
conversion that failed part way through was never rolled back, and the batch
script could not terminate if an object refused to convert.

This fork takes the OpenContent code as its base, takes the attribute
pre-selection and packaging from the Netgen fork, and fixes what neither had.

## What it fixes

### Nothing is left half converted

A conversion rewrites attributes across every version and every language of an
object. The predecessors opened a transaction, and then on any problem either
called `die()` from inside the attribute loop or threw from inside it. Neither
rolled back. The request ended with some attributes moved to the new class and
the rest still on the old one, and the object's own `contentclass_id` was
changed *after* the commit, so a failure there left attributes of the new class
on an object still declaring the old.

Everything that touches content now runs inside one transaction, including the
class change, and rolls back as a unit. `convertObject()` returns `false` with a
message instead of ending the request:

```php
$error = null;
if ( !conversionFunctions::convertObject( $objectID, $classID, $mapping, $error ) )
{
    // content is exactly as it was
}
```

### The mapping is validated before anything is written

A mapping naming an attribute that neither class has used to surface as a fatal
part way through the rewrite, with earlier attributes already stored. The whole
mapping is checked against both class data maps first, so a bad mapping is
refused while the content is still untouched.

### The batch script terminates

`classconvert.php` paged through objects with `'offset' => 0` hardcoded. That
works only because a converted object stops matching the class filter and drops
out of the next page. An object that *fails* to convert keeps matching, so the
same page was fetched, failed, and fetched again forever. The window now steps
over failures, and a batch in which nothing converted stops the run.

### Text is escaped before it becomes XML

The text-to-`ezxmltext` converter built its markup by substituting the raw field
value into a string. Any content holding `&` or `<` — "Tom & Jerry", "a < b", a
pasted HTML fragment — produced XML the parser rejected or silently
reinterpreted, losing part of the field. The value is escaped, and a parse
failure is reported rather than stored as an empty field.

### The relation-list converter runs at all

It used `eZXML::domTree()` and `get_elements_by_tagname()`, the PHP 4 XML
wrapper the kernel dropped long ago. Rewritten on `DOMDocument`.

### `ezstring` to `ezxmltext` is reachable

`changeclass.ini` declared `[ezstring]` twice. A repeated group does not merge —
the second occurrence wins — so the destinations named in the first were
unreachable and `ezstring` to `ezxmltext` silently did not exist. There is one
group per source datatype now, dispatching on the destination.

### The result is reported

`convertObject()`'s return value was discarded, so a refused conversion rendered
the same success page as a real one. The page now states plainly whether the
content changed, and why not.

### Conversion is a policy

The module declared no functions, so the only possible grant was
all-or-nothing on the module. There is a `changeclass/convert` policy function
on all three views.

### Attribute pre-selection

Taken from the Netgen fork: the mapping form pre-selects the source attribute
whose identifier matches the destination, instead of always defaulting to the
first attribute in the list.

## Installing

```
composer require se7enxweb/expchangeclass
```

Then enable it in `site.ini`:

```
[ExtensionSettings]
ActiveExtensions[]=expchangeclass
```

Clear the caches **as the web server user** — running `ezcache.php` as root
prints a warning and exits without clearing anything:

```
sudo -u www-data php bin/php/ezcache.php --clear-all
```

Grant the `changeclass/convert` policy to the roles that should be allowed to
convert content.

## Using it

See [doc/USAGE.md](doc/USAGE.md).

## Before you run it

Conversion rewrites published content in place, across every version and
language. It is not undo-able from the interface. **Take a database backup
first**, and try it on one object before a batch.

## Licence

GNU General Public License v2.0 or later. Original work copyright (C) 2007
Bartek Modzelewski.
