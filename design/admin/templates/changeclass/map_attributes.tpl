{* changeclass/map_attributes/<NodeID>/<ClassID>: step 2 of a class change, the mapping and the preview of what the
   change does, in every admin design. Only shared components and tokens; no inline CSS or script. Variables: node,
   object, plan (expChangeClassService::preview()), errors, warnings (translated), source_class, dest_class, facts,
   command, form_uri, message. *}
<div class="context-block a4-page">

<div class="box-header"><div class="box-ml">
<h1 class="context-title"><span class="a4-icon a4-bi-diagram-3 is-large" aria-hidden="true"></span> {'Change %name from %class into %new_class'|i18n( 'extension/expchangeclass',, hash( '%name', $node.name, '%class', $facts.source_class.name, '%new_class', $facts.dest_class.name ) )|wash}</h1>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

{include uri='design:changeclass/parts/steps.tpl' step=2}

<p class="a4-intro">{'Nothing is written until you apply the change. Then a restore point of every version and language is taken first, the change runs in one transaction that is rolled back as a whole on any error, and the object\'s caches and search index entry are renewed.'|i18n( 'extension/expchangeclass' )}</p>

{include uri='design:changeclass/parts/message.tpl' message=$message}
{include uri='design:changeclass/parts/problems.tpl' errors=$errors warnings=$warnings}

<form method="post" action={$form_uri|ezurl} class="a4-stack">
<input type="hidden" name="PlanToken" value="{$plan.token|wash}" />

<ul class="a4-figures" aria-label="{'What the change touches'|i18n( 'extension/expchangeclass' )|wash}">
    <li class="a4-figure"><strong>{$plan.stats.versions}</strong><span>{'versions'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$plan.stats.languages}</strong><span>{'languages'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$plan.stats.carried}</strong><span>{'attributes carried over'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$plan.stats.dropped}</strong><span>{'old attributes dropped'|i18n( 'extension/expchangeclass' )}</span></li>
    <li class="a4-figure"><strong>{$plan.stats.drafts}</strong><span>{'drafts'|i18n( 'extension/expchangeclass' )}</span></li>
</ul>

<div id="exp-cc-preview">
{include uri='design:changeclass/parts/mapping_table.tpl' plan=$plan show_values=true()}
</div>

<details class="a4-fold">
    <summary>{'The same from a shell'|i18n( 'extension/expchangeclass' )}</summary>
    <div class="a4-fold-body">
        <p class="a4-meta">{'Without --apply the command only shows this preview. Add --apply to change the class; --format=json prints the plan for scripts.'|i18n( 'extension/expchangeclass' )}</p>
        <pre class="a4-pre">{$command|wash}</pre>
    </div>
</details>

<div class="a4-toolbar is-sticky">
    <p class="a4-meta">{if $plan.ok}{'Changed a select? Update the preview first: the change applies exactly what the preview shows.'|i18n( 'extension/expchangeclass' )}{else}{'Fix what is listed at the top before the class can be changed.'|i18n( 'extension/expchangeclass' )}{/if}</p>
    <div class="a4-actions">
        <button class="a4-btn" type="submit" name="BackButton" value="1">{'Back'|i18n( 'extension/expchangeclass' )}</button>
        <button class="a4-btn" type="submit" name="CancelButton" value="1">{'Cancel'|i18n( 'extension/expchangeclass' )}</button>
        <button class="a4-btn" type="submit" name="PreviewButton" value="1">{'Update the preview'|i18n( 'extension/expchangeclass' )}</button>
        <button class="a4-btn a4-btn-primary" type="submit" name="ApplyButton" value="1"{if $plan.ok|not} disabled="disabled"{/if}>{'Change the class'|i18n( 'extension/expchangeclass' )}</button>
    </div>
</div>

</form>

</div></div></div>

</div>
