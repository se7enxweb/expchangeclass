# Bulk changes and the command line

## Many objects in the background

`changeclass/bulk` (Setup > Change class > **Change many objects**, or **Change the other … objects** on a result page):
choose the class, the class it becomes and the node below which every published object of that class is changed.
The page shows how many objects that is, the first ten with a link to the full preview of each, and the mapping for
all of them. **Start the background job** ("Verify it's you" first) creates a content job and opens its progress page
`content/job/<id>`, which you can leave; the job can be cancelled and resumed there.

The job changes one object per batch, each with its own plan, restore point and transaction, as you. An object whose
plan refuses (locked, a required access missing, ...) or whose change is rolled back is skipped and listed with the
reason in the job's result; the job goes on. It needs Exponential 6.0.15 or later (content jobs).

## exp:changeclass

```
./console exp:changeclass --object=57 --class=blog_post                      # preview, nothing is written
./console exp:changeclass --object=57 --class=blog_post --map=body=intro --apply
./console exp:changeclass --from=article --class=blog_post --subtree=2 --apply
./console exp:changeclass --object=57 --class=blog_post --format=json        # the plan for scripts
./console exp:changeclass --list
./console exp:changeclass --restore=<id> --apply
./console exp:changeclass --discard=<id> --apply
./console exp:changeclass --purge=90 --apply
```

Also `ext:expchangeclass:changeclass`. Without `--apply` nothing is written. `--map=<new>=<old>` (repeatable,
`--map=<new>=` leaves it empty); without `--map` the proposed mapping is used (same identifier, then same datatype).
`--user=<login>` acts as that user (default `migration.ini [RunSettings] RunAsUser`, else `admin`), so its policies
are checked as in the administration. Exit codes: 0 done, 1 refused or failed, 2 usage error.

`scripts/classconvert.php` with its parameter files still works and goes through the same plan and restore point.
