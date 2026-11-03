{* changeclass/bulk[/<ClassID>/<NewClassID>/<NodeID>]: every object of a class in a subtree, as a background job, in
   every admin design. Only shared components and tokens. Variables: chosen, classes, source_id, dest_id, node_id,
   node, jobs_available, message; when chosen also plan, errors, warnings, source_class, dest_class, count, sample,
   token, form_uri, command. *}
<div class="context-block a4-page">

<div class="box-header"><div class="box-ml">
<h1 class="context-title"><span class="a4-icon a4-bi-diagram-3 is-large" aria-hidden="true"></span> {'Change many objects'|i18n( 'extension/expchangeclass' )}</h1>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

<p class="a4-intro">{'Every published object of a class below a node, changed with one mapping in the background. You can leave the progress page; each object gets its own check, restore point and transaction, and one that cannot be changed is skipped with the reason.'|i18n( 'extension/expchangeclass' )}</p>

{include uri='design:changeclass/parts/message.tpl' message=$message}
{if $jobs_available|not}<div class="a4-notice is-warn" role="status"><p>{'Background jobs need Exponential 6.0.15 or later. Use the command shown below.'|i18n( 'extension/expchangeclass' )}</p></div>{/if}

<div class="a4-stack">

<form method="post" action={'changeclass/bulk'|ezurl}>
<section class="a4-card" aria-labelledby="exp-cc-choose-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-choose-title">{'Classes and subtree'|i18n( 'extension/expchangeclass' )}</h2></div>
    <div class="a4-form a4-grid">
        <div class="a4-field">
            <label for="exp-cc-from">{'Objects of the class'|i18n( 'extension/expchangeclass' )}</label>
            <select id="exp-cc-from" name="SourceClassID">{foreach $classes as $class}<option value="{$class.id}"{if eq( $class.id, $source_id )} selected="selected"{/if}>{$class.name|wash} ({$class.identifier|wash})</option>{/foreach}</select>
        </div>
        <div class="a4-field">
            <label for="exp-cc-into">{'Change into'|i18n( 'extension/expchangeclass' )}</label>
            <select id="exp-cc-into" name="DestinationClassID">{foreach $classes as $class}<option value="{$class.id}"{if eq( $class.id, $dest_id )} selected="selected"{/if}>{$class.name|wash} ({$class.identifier|wash})</option>{/foreach}</select>
        </div>
        <div class="a4-field">
            <label for="exp-cc-node">{'Below the node (ID)'|i18n( 'extension/expchangeclass' )}</label>
            <input id="exp-cc-node" type="number" min="1" name="SubtreeNodeID" value="{$node_id|wash}" required="required" />
            {if $node}<p class="a4-help">{$node.name|wash}</p>{/if}
        </div>
    </div>
    <div class="a4-actions"><button class="a4-btn" type="submit" name="ChooseButton" value="1">{'Show the preview'|i18n( 'extension/expchangeclass' )}</button></div>
</section>
</form>

{if $chosen}
{include uri='design:changeclass/parts/problems.tpl' errors=$errors warnings=$warnings}

<form method="post" action={$form_uri|ezurl} class="a4-stack">
<input type="hidden" name="PlanToken" value="{$token|wash}" />

<section class="a4-card" aria-labelledby="exp-cc-objects-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-objects-title">{'%count objects of %class below %node'|i18n( 'extension/expchangeclass',, hash( '%count', $count, '%class', $source_class.name, '%node', $node.name ) )|wash}</h2></div>
    {if $sample}
    <ul class="a4-plain">
    {foreach $sample as $object}<li><a href={concat( 'content/view/full/', $object.main_node_id )|ezurl}>{$object.name|wash}</a> <a class="a4-meta" href={concat( 'changeclass/map_attributes/', $object.main_node_id, '/', $dest_class.id )|ezurl}>{'preview this one'|i18n( 'extension/expchangeclass' )}</a></li>{/foreach}
    </ul>
    {if gt( $count, $sample|count )}<p class="a4-meta">{'and %count more'|i18n( 'extension/expchangeclass',, hash( '%count', sub( $count, $sample|count ) ) )|wash}</p>{/if}
    {/if}
</section>

{include uri='design:changeclass/parts/mapping_table.tpl' plan=$plan show_values=false()}

<details class="a4-fold">
    <summary>{'The same from a shell'|i18n( 'extension/expchangeclass' )}</summary>
    <div class="a4-fold-body"><pre class="a4-pre">{$command|wash} --apply</pre></div>
</details>

<div class="a4-toolbar is-sticky">
    <p class="a4-meta">{'Changed a select? Update the preview first: the job applies exactly what the preview shows.'|i18n( 'extension/expchangeclass' )}</p>
    <div class="a4-actions">
        <button class="a4-btn" type="submit" name="PreviewButton" value="1">{'Update the preview'|i18n( 'extension/expchangeclass' )}</button>
        <button class="a4-btn a4-btn-primary" type="submit" name="StartButton" value="1"{if or( $plan.ok|not, $jobs_available|not )} disabled="disabled"{/if}>{'Start the background job'|i18n( 'extension/expchangeclass' )}</button>
    </div>
</div>
</form>
{/if}

</div>

</div></div></div>

</div>
