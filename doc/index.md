# Change class

Change the content class of content that exists: an article that should be a blog post, a folder that should be a
landing page. The object keeps its id, its locations, its versions and its languages; only its class changes, and
each attribute of the new class takes the content of an attribute of the old one.

Every change is shown before anything is written, runs in one database transaction, and keeps a restore point that
undoes it.

## Changing the class of one item

1. In the content tree or the sub-items list, open the context menu of the item and choose **Change content class**.
2. **Choose the new class.** The page shows what the object is now: its class, versions, languages, sub-items and the
   objects that relate to it. Only the classes your role allows are offered.
3. **Map the attributes and preview.** For each attribute of the new class choose the old attribute whose content it
   takes; only attributes whose content can be carried into it are offered. The preview shows, per language, what each
   attribute holds now, how it is carried over (*Carried over*, *Carried over, other datatype*, *Converted*, *Copied*,
   *Stays empty*), and which old attributes nothing takes over. Changed a select? Choose **Update the preview**: the
   change applies exactly what the preview shows.
4. **Change the class.** "Verify it's you" may ask for your password first. The result page says what was done and
   offers **Restore**.

See [The preview and the mapping](preview-and-mapping.md) for what the preview checks, and
[Restore points](restore-points.md) for undoing a change.

## Overview: Setup > Change class

`changeclass/dashboard` lists the recent class changes with their state and a **Restore** for each one that can still
be undone. Administrators see every user's changes and the **Editors** card that gives the Editor role the policies to
change classes ([Policies for editors](policies.md)); editors see their own changes.

## Many objects at once

**Change many objects** (`changeclass/bulk`) changes every object of a class below a node with one mapping, as a
background job with a progress page. See [Bulk changes and the command line](bulk-and-command-line.md).

## Pages

- [The preview and the mapping](preview-and-mapping.md)
- [Restore points](restore-points.md)
- [Policies for editors](policies.md)
- [Bulk changes and the command line](bulk-and-command-line.md)
- [Migrations](migrations.md)
- [Converters and settings](USAGE.md)
