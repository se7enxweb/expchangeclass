# The preview and the mapping

`changeclass/map_attributes/<node>/<class>` shows the mapping and its preview on one page. Nothing is written until
**Change the class** is pressed.

## How content is carried over

| Badge | Meaning |
|---|---|
| Carried over | the same datatype: the attribute row moves to the new class unchanged |
| Carried over, other datatype | a pair of `changeclass.ini [SimpleConversion]` (e.g. `ezstring` and `eztext`) |
| Converted | a converter of `changeclass.ini` rewrites the data (e.g. `ezstring` into `ezxmltext`, the text escaped) |
| Copied | an old attribute already carried into another new one: its content is copied, version by version and language by language |
| Stays empty | no old attribute: the new attribute starts with its class default |

An old attribute that nothing takes over is listed under **Old attributes nothing takes over**, with what it holds per
language. Its content is no longer shown after the change, but it is not deleted: the restore point keeps it until the
restore point is discarded.

## What refuses the change

The **Change the class** button stays disabled, and the reasons are listed at the top, when:

- the object is of that class already, is in the trash, or is a user account;
- the object is locked (object state `ez_lock/locked`);
- you may not edit it (`content/edit`), or your role does not allow this pair of classes (`changeclass/convert`);
- a background content job works on its part of the tree;
- a mapping names an attribute neither class has, or a pair of datatypes that cannot be converted;
- the new class has a datatype listed in `changeclass.ini [General] UnsupportedDataTypeArray[]` (`ezuser`).

## What you should know first

Listed in yellow, the change is still possible:

- an old attribute with content is dropped (per language);
- a required attribute of the new class stays empty (the next edit asks for a value before it can be published);
- an old attribute is copied into a second new one;
- drafts are changed too, and drafts of other users (tell them before they publish an edit that was open);
- the object has sub-items but the new class is no container;
- other objects relate to it through a relation attribute that may only accept certain classes.

## What was previewed is what is applied

The form carries a token of the object's current version, its modification time, both classes and the mapping. When
anything of that changed between the preview and **Change the class** (another editor published, a select was changed
without **Update the preview**), nothing is written and the new preview is shown.

## Every version and every language

The change works on the object's attribute rows as they are stored: every version and every language a row exists
for, also a language the siteaccess does not list or one only an older version has. The relation rows of relation
attributes follow their attribute. After the change the object's content view and template block caches and its search
index entry are renewed, and the change is recorded in the audit (`content.expchangeclass_object.convert`).
