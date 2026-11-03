# Migrations

With Exponential 6.0.15 or later the extension adds the step type `change_class` to YAML migrations:

```yaml
- type: change_class
  objects: [ "remote:about-us", 57 ]
  from: folder                    # optional: only objects of this class, others fail the step
  class: landing_page
  mapping: { name: title, short_description: intro }   # new attribute: old attribute ('' leaves it empty)
  references:
    points: restore_points
```

The dry run lists the plan of each object; the run changes them exactly as the administration does (plan, restore
point, one transaction each) and fails the step on the first object that refuses. The values for `references:` are
`changed` and `restore_points` (comma-separated); `./console exp:changeclass --restore=<id> --apply` undoes one.
