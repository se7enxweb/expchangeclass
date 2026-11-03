{* changeclass/dashboard: the overview of class changes, in every admin design. Only shared components and tokens.
   Variables: restore_points, counts (state => count), state, states, administrate, can_bulk, policies
   (expChangeClassPolicies::state(), with changeclass/administrate), jobs_available, directory, message. *}
<div class="context-block a4-page">

<div class="box-header"><div class="box-ml">
<h1 class="context-title"><span class="a4-icon a4-bi-diagram-3 is-large" aria-hidden="true"></span> {'Change class'|i18n( 'extension/expchangeclass' )}</h1>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

<p class="a4-intro">{'Change the class of content that exists: an article that should be a blog post, a folder that should be a landing page. Open the context menu of an item in the content tree or the sub-items list and choose "Change content class". You see what happens to each attribute before anything is written, and every change keeps a restore point to undo it.'|i18n( 'extension/expchangeclass' )}</p>

{include uri='design:changeclass/parts/message.tpl' message=$message}

<ul class="a4-figures">
    <li class="a4-figure"><strong>{$counts.applied}</strong><span>{'restorable changes'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$counts.restored}</strong><span>{'undone'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$counts.discarded}</strong><span>{'restore points discarded'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$counts.failed}</strong><span>{'rolled back'|i18n( 'extension/expchangeclass' )}</span></li>
</ul>

<div class="a4-stack">

<section class="a4-card" aria-labelledby="exp-cc-list-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-list-title">{if $administrate}{'Recent class changes'|i18n( 'extension/expchangeclass' )}{else}{'Your recent class changes'|i18n( 'extension/expchangeclass' )}{/if}</h2></div>
    <form method="post" action={'changeclass/dashboard'|ezurl} class="a4-filterbar" aria-label="{'Filter'|i18n( 'extension/expchangeclass' )|wash}">
        <div class="a4-field">
            <label for="exp-cc-state">{'State'|i18n( 'extension/expchangeclass' )}</label>
            <select id="exp-cc-state" name="State">
                <option value=""{if eq( $state, '' )} selected="selected"{/if}>{'Every state'|i18n( 'extension/expchangeclass' )}</option>
                <option value="applied"{if eq( $state, 'applied' )} selected="selected"{/if}>{'Changed, restorable'|i18n( 'extension/expchangeclass' )}</option>
                <option value="restored"{if eq( $state, 'restored' )} selected="selected"{/if}>{'Undone'|i18n( 'extension/expchangeclass' )}</option>
                <option value="discarded"{if eq( $state, 'discarded' )} selected="selected"{/if}>{'Restore point discarded'|i18n( 'extension/expchangeclass' )}</option>
                <option value="failed"{if eq( $state, 'failed' )} selected="selected"{/if}>{'Rolled back'|i18n( 'extension/expchangeclass' )}</option>
            </select>
        </div>
        <div class="a4-actions"><button class="a4-btn" type="submit" name="FilterButton" value="1">{'Filter'|i18n( 'extension/expchangeclass' )}</button></div>
    </form>
    {if $restore_points}
    <div class="a4-table-wrap">
    <table class="a4-table">
    <thead><tr>
        <th scope="col">{'When'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col">{'Object'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col">{'Change'|i18n( 'extension/expchangeclass' )}</th>
        {if $administrate}<th scope="col">{'By'|i18n( 'extension/expchangeclass' )}</th>{/if}
        <th scope="col">{'State'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col"><span class="a4-sr-only">{'Actions'|i18n( 'extension/expchangeclass' )}</span></th>
    </tr></thead>
    <tbody>
    {foreach $restore_points as $point}
    <tr>
        <td class="a4-nowrap"><a href={concat( 'changeclass/result/', $point.id )|ezurl}>{$point.created|l10n( 'shortdatetime' )}</a></td>
        <td>{if $point.object.main_node_id}<a href={concat( 'content/view/full/', $point.object.main_node_id )|ezurl}>{$point.object.name|wash}</a>{else}{$point.object.name|wash}{/if}</td>
        <td>{$point.source_class.name|wash} &rarr; {$point.dest_class.name|wash}</td>
        {if $administrate}<td>{$point.user_login|wash} <span class="a4-meta">{$point.via|wash}</span></td>{/if}
        <td>{include uri='design:changeclass/parts/state.tpl' state=$point.state}</td>
        <td>{if eq( $point.state, 'applied' )}
            <form method="post" action={concat( 'changeclass/dashboard', cond( $state, concat( '/(state)/', $state ), '' ) )|ezurl}>
                <input type="hidden" name="RestorePointID" value="{$point.id|wash}" />
                <details class="a4-confirm">
                    <summary>{'Restore…'|i18n( 'extension/expchangeclass' )}</summary>
                    <div><p>{'%name becomes %class again, with its attributes as they were.'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name, '%class', $point.source_class.name ) )|wash}</p>
                    <button class="a4-btn a4-btn-primary a4-btn-small" type="submit" name="RestoreButton" value="1">{'Restore'|i18n( 'extension/expchangeclass' )}</button></div>
                </details>
            </form>
        {/if}</td>
    </tr>
    {/foreach}
    </tbody>
    </table>
    </div>
    {else}
    <p class="a4-empty">{'No class changes yet.'|i18n( 'extension/expchangeclass' )}</p>
    {/if}
    <p class="a4-meta">{'Restore points are kept in %directory until they are discarded or purged (./console exp:changeclass --purge=<days> --apply).'|i18n( 'extension/expchangeclass',, hash( '%directory', $directory ) )|wash}</p>
</section>

{if $can_bulk}
<section class="a4-card" aria-labelledby="exp-cc-bulk-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-bulk-title">{'Change many objects'|i18n( 'extension/expchangeclass' )}</h2></div>
    <p>{'Every object of a class in a part of the tree, with one mapping, as a background job with a progress page. Each object gets its own preview check, restore point and transaction; an object that cannot be changed is skipped with the reason.'|i18n( 'extension/expchangeclass' )}</p>
    <div class="a4-actions"><a class="a4-btn" href={'changeclass/bulk'|ezurl}>{'Choose the classes and the subtree'|i18n( 'extension/expchangeclass' )}</a></div>
</section>
{/if}

{if $policies}
<section class="a4-card" aria-labelledby="exp-cc-policies-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-policies-title">{'Editors'|i18n( 'extension/expchangeclass' )}</h2></div>
    {if $policies.exists}
    <p>{'Gives the role %role the policies changeclass/convert (change the class of an object they may edit) and changeclass/restore (undo such a change), without limitations: the role\'s assignment limits them as it limits the rest of the role. Nothing is added twice; Remove takes the role\'s changeclass policies away again.'|i18n( 'extension/expchangeclass',, hash( '%role', $policies.name ) )|wash}</p>
    <ul class="a4-badges">
        {foreach $policies.held as $function => $held}
        <li class="a4-badge {if $held}is-ok{/if}">changeclass/{$function|wash}: {if $held}{'held'|i18n( 'extension/expchangeclass' )}{else}{'not held'|i18n( 'extension/expchangeclass' )}{/if}</li>
        {/foreach}
    </ul>
    <form method="post" action={'changeclass/dashboard'|ezurl}>
        <input type="hidden" name="Role" value="{$policies.name|wash}" />
        <div class="a4-toolbar is-bottom">
            <p class="a4-meta">{'Both buttons ask you to confirm it is you. The same from a shell: ./console exp:addchangeclasspolicies --apply (dry run without --apply).'|i18n( 'extension/expchangeclass' )}</p>
            <div class="a4-actions">
                <button class="a4-btn a4-btn-primary" type="submit" name="GrantEditorPoliciesButton" value="1"{if eq( $policies.to_grant, 0 )} disabled="disabled"{/if}>{'Allow editors to change classes'|i18n( 'extension/expchangeclass' )}</button>
                <button class="a4-btn a4-btn-outline-danger" type="submit" name="RevokeEditorPoliciesButton" value="1"{if $policies.revoke|count|eq( 0 )} disabled="disabled"{/if}>{'Remove'|i18n( 'extension/expchangeclass' )}</button>
            </div>
        </div>
    </form>
    {else}
    <p class="a4-empty">{'There is no role %role on this installation. Give the policies to another role with ./console exp:addchangeclasspolicies --role=<name> --apply.'|i18n( 'extension/expchangeclass',, hash( '%role', $policies.name ) )|wash}</p>
    {/if}
</section>
{/if}

<section class="a4-card" aria-labelledby="exp-cc-cli-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-cli-title">{'From a shell'|i18n( 'extension/expchangeclass' )}</h2></div>
    <pre class="a4-pre">./console exp:changeclass --object=&lt;id&gt; --class=&lt;identifier&gt;            # preview
./console exp:changeclass --object=&lt;id&gt; --class=&lt;identifier&gt; --apply    # change
./console exp:changeclass --from=&lt;identifier&gt; --class=&lt;identifier&gt; --subtree=&lt;node&gt; --apply
./console exp:changeclass --restore=&lt;restore point&gt; --apply
./console exp:changeclass --list
./console exp:addchangeclasspolicies --apply</pre>
</section>

</div>

</div></div></div>

</div>
