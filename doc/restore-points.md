# Restore points

Before a class change writes anything, every attribute row of the object (all versions and languages, as stored), its
relation rows and its class are written to a restore point in `var/<site>/expchangeclass/restorepoints/<id>.json`.
Without it the change does not start.

## Restoring

The result page of a change (`changeclass/result/<id>`) and the overview offer **Restore**. It puts every row back as it
was and the old class with it, in one transaction. The restored rows are byte for byte the rows before the change.

Restoring is refused, with the reason, when:

- the object was edited after the change (a version was added or removed): restoring would lose that work; change its
  class back instead;
- the object is no longer of the class it was changed into;
- the old class lost an attribute since the change, or no longer exists.

## Discarding

The content of the attributes a change dropped stays where it was (files, image aliases, the datatype's own rows)
until its restore point is **discarded**. Discarding removes that data for good, the way removing an attribute always
did, and the change can no longer be undone. The record of the change stays.

```
./console exp:changeclass --discard=<id> --apply
./console exp:changeclass --purge=90 --apply      # discard restore points older than 90 days
```

## States

| State | Meaning |
|---|---|
| Changed, restorable (`applied`) | the change is in place and can be undone |
| Undone (`restored`) | the object was put back |
| Restore point discarded (`discarded`) | the dropped data is gone, the change stays |
| Rolled back (`failed`) | the transaction was rolled back: nothing was changed |
| Running (`pending`) | written, the change had not committed yet |

Restoring and discarding ask "Verify it's you" first and are recorded in the audit
(`content.expchangeclass_object.restore`, `content.expchangeclass_restorepoint.discard`). On a cluster the directory
must be shared, as the content jobs' is.
