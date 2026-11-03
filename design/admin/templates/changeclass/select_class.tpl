{* changeclass/select_class/<NodeID>: step 1 of a class change, in every admin design (admin, admin3, admin4, admin4l
   and the designs built on them). Only the shared components of components.css and the --a4-* tokens; no inline
   CSS or script. Variables: node, object, source_class, facts (expChangeClassService::facts()), classes (id,
   identifier, name, group, allowed), allowed_count, can_convert, restore_points, message. *}
<div class="context-block a4-page">

<div class="box-header"><div class="box-ml">
<h1 class="context-title"><span class="a4-icon a4-bi-diagram-3 is-large" aria-hidden="true"></span> {'Change the class of %name'|i18n( 'extension/expchangeclass',, hash( '%name', $node.name ) )|wash}</h1>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

{include uri='design:changeclass/parts/steps.tpl' step=1}

<p class="a4-intro">{'The object keeps its id, its locations, its versions and its languages; only its class changes. The next step shows exactly what happens to each attribute before anything is written, and a restore point lets you undo the change.'|i18n( 'extension/expchangeclass' )}</p>

{include uri='design:changeclass/parts/message.tpl' message=$message}

<div class="a4-stack">

<section class="a4-card" aria-labelledby="exp-cc-object-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-object-title">{'The object now'|i18n( 'extension/expchangeclass' )}</h2></div>
    <dl class="a4-facts">
        <div><dt>{'Name'|i18n( 'extension/expchangeclass' )}</dt><dd><a href={$node.url_alias|ezurl}>{$node.name|wash}</a></dd></div>
        <div><dt>{'Class'|i18n( 'extension/expchangeclass' )}</dt><dd>{$source_class.name|wash} <span class="a4-tag is-mono">{$source_class.identifier|wash}</span></dd></div>
        <div><dt>{'Object ID'|i18n( 'extension/expchangeclass' )}</dt><dd>{$object.id}</dd></div>
        <div><dt>{'Versions'|i18n( 'extension/expchangeclass' )}</dt><dd>{$facts.object.versions|count}</dd></div>
        <div><dt>{'Languages'|i18n( 'extension/expchangeclass' )}</dt><dd><span class="a4-badges">{foreach $facts.object.languages as $language}<span class="a4-tag is-mono">{$language|wash}</span>{/foreach}</span></dd></div>
        <div><dt>{'Sub-items'|i18n( 'extension/expchangeclass' )}</dt><dd>{$node.children_count}</dd></div>
        <div><dt>{'Objects relating to it'|i18n( 'extension/expchangeclass' )}</dt><dd>{$facts.reverse_relations}</dd></div>
        <div><dt>{'Modified'|i18n( 'extension/expchangeclass' )}</dt><dd>{$object.modified|l10n( 'shortdatetime' )}</dd></div>
    </dl>
    <details class="a4-fold">
        <summary>{'Attributes of %class (%count)'|i18n( 'extension/expchangeclass',, hash( '%class', $source_class.name, '%count', $facts.source_class.attributes|count ) )|wash}</summary>
        <div class="a4-fold-body">
        <ul class="a4-plain">{foreach $facts.source_class.attributes as $attribute}<li>{$attribute.name|wash} <span class="a4-tag is-mono">{$attribute.identifier|wash}</span> <span class="a4-tag">{$attribute.datatype|wash}</span></li>{/foreach}</ul>
        </div>
    </details>
</section>

<form method="post" action={concat( 'changeclass/select_class/', $node.node_id )|ezurl}>
<section class="a4-card" aria-labelledby="exp-cc-class-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-class-title">{'The new class'|i18n( 'extension/expchangeclass' )}</h2></div>
    {if $allowed_count|gt( 0 )}
    <div class="a4-form">
        <div class="a4-field">
            <label for="exp-cc-class">{'Change into'|i18n( 'extension/expchangeclass' )}</label>
            {def $current_group = '-' $group_open = false()}
            <select id="exp-cc-class" name="DestinationClassID" required="required">
            {foreach $classes as $class}{if $class.allowed}
                {if ne( $class.group, $current_group )}{if $group_open}</optgroup>{/if}<optgroup label="{if $class.group}{$class.group|wash}{else}{'Other'|i18n( 'extension/expchangeclass' )|wash}{/if}">{set $current_group = $class.group}{set $group_open = true()}{/if}
                <option value="{$class.id}">{$class.name|wash} ({$class.identifier|wash})</option>
            {/if}{/foreach}
            {if $group_open}</optgroup>{/if}
            </select>
            {undef $current_group $group_open}
            {if $allowed_count|lt( $classes|count )}<p class="a4-help">{'%count classes are left out: your role does not allow changing this object into them.'|i18n( 'extension/expchangeclass',, hash( '%count', sub( $classes|count, $allowed_count ) ) )|wash}</p>{/if}
        </div>
    </div>
    {else}
    <p class="a4-empty">{'Your role does not allow changing this object into any class (policy changeclass/convert).'|i18n( 'extension/expchangeclass' )}</p>
    {/if}
</section>

<div class="a4-toolbar">
    <p class="a4-meta">{'Nothing is changed yet.'|i18n( 'extension/expchangeclass' )}</p>
    <div class="a4-actions">
        <button class="a4-btn" type="submit" name="CancelButton" value="1" formnovalidate="formnovalidate">{'Cancel'|i18n( 'extension/expchangeclass' )}</button>
        {if $allowed_count|gt( 0 )}<button class="a4-btn a4-btn-primary" type="submit" name="SelectDestinationClassButton" value="1">{'Continue to the mapping'|i18n( 'extension/expchangeclass' )}</button>{/if}
    </div>
</div>
</form>

{if $restore_points}
<section class="a4-card" aria-labelledby="exp-cc-history-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-history-title">{'Earlier class changes of this object'|i18n( 'extension/expchangeclass' )}</h2></div>
    <div class="a4-table-wrap">
    <table class="a4-table">
    <thead><tr><th scope="col">{'When'|i18n( 'extension/expchangeclass' )}</th><th scope="col">{'Change'|i18n( 'extension/expchangeclass' )}</th><th scope="col">{'State'|i18n( 'extension/expchangeclass' )}</th></tr></thead>
    <tbody>
    {foreach $restore_points as $point}
    <tr>
        <td><a href={concat( 'changeclass/result/', $point.id )|ezurl}>{$point.created|l10n( 'shortdatetime' )}</a></td>
        <td>{$point.source_class.name|wash} &rarr; {$point.dest_class.name|wash}</td>
        <td>{include uri='design:changeclass/parts/state.tpl' state=$point.state}</td>
    </tr>
    {/foreach}
    </tbody>
    </table>
    </div>
</section>
{/if}

</div>

</div></div></div>

</div>
