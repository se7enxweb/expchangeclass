# Policies for editors

| Policy | Allows | Limitations |
|---|---|---|
| `changeclass/convert` | changing the class of one object (the object's own `content/edit` is still needed) | Class (the class now), NewClass (the class it becomes), Section, Node, Subtree |
| `changeclass/restore` | undoing such a change from its restore point | the same; NewClass is the class the object gets back |
| `changeclass/bulk` | changing every object of a class below a node, as a background job (each object is still checked against `changeclass/convert`) | Class, NewClass |
| `changeclass/administrate` | the overview of every user's changes, discarding any restore point, the Editors button | none |

The limitations of the role assignment (Section, Subtree) apply as they do to every policy of the role.

## Letting editors change classes

**From the administration:** Setup > Change class, card **Editors**, **Allow editors to change classes**. It asks
"Verify it's you" first. **Remove** takes the policies away again.

**From a shell:**

```
./console exp:addchangeclasspolicies                 # dry run: what would be added
./console exp:addchangeclasspolicies --apply         # add them
./console exp:addchangeclasspolicies --apply --revoke
```

Both give the role **Editor** exactly:

- `changeclass/convert`, without limitations
- `changeclass/restore`, without limitations

so the role's assignment limits them as it limits the rest of the role (the Editors group of a new installation:
Content and Media). Nothing is added twice: a function the role holds already in any form (also limited,
`changeclass/*` or `*/*`) is left alone, so the command can run again. `changeclass/administrate` is never given.

Options of the command:

| Option | Effect |
|---|---|
| `--role=<name or id>` | another role than Editor |
| `--with-bulk` | also `changeclass/bulk` |
| `--class=<identifier>` | limitation Class (repeatable) |
| `--new-class=<identifier>` | limitation NewClass (repeatable) |
| `--section=<identifier or id>` | limitation Section (repeatable) |
| `--subtree=<node id>` | limitation Subtree (repeatable) |
| `--revoke` | remove the role's `changeclass/convert`, `restore` and `bulk` policies |

For example, editors may turn articles into blog posts and nothing else:

```
./console exp:addchangeclasspolicies --apply --class=article --new-class=blog_post
```

Granting and revoking are recorded in the audit (`access.expchangeclass_policies.grant`, `.revoke`).
