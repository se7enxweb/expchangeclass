{* The mapping form: a row per attribute of the new class with the old attribute it takes its content from, how, and
   (show_values) what it holds per language now. Then what is dropped. Parameters: plan, show_values (bool). *}
<section class="a4-card" aria-labelledby="exp-cc-mapping-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-mapping-title">{'Attributes of the new class'|i18n( 'extension/expchangeclass' )}</h2></div>
    <p class="a4-meta">{'Choose for each attribute of the new class the attribute of the old class whose content it takes. Only attributes whose content can be carried into it are offered.'|i18n( 'extension/expchangeclass' )}</p>
    <div class="a4-table-wrap">
    <table class="a4-table">
    <thead><tr>
        <th scope="col">{'New attribute'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col">{'Takes its content from'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col">{'How'|i18n( 'extension/expchangeclass' )}</th>
        {if $show_values}<th scope="col">{'Content now'|i18n( 'extension/expchangeclass' )}</th>{/if}
    </tr></thead>
    <tbody>
    {foreach $plan.rows as $row}
    <tr>
        <th scope="row">
            <label for="exp-cc-map-{$row.identifier|wash}">{$row.name|wash}</label>
            <div class="a4-badges"><span class="a4-tag is-mono">{$row.identifier|wash}</span><span class="a4-tag">{$row.datatype|wash}</span>{if $row.required}<span class="a4-badge is-warn">{'Required'|i18n( 'extension/expchangeclass' )}</span>{/if}</div>
        </th>
        <td>
            <select id="exp-cc-map-{$row.identifier|wash}" name="SourceAttribute[{$row.identifier|wash}]">
                <option value=""{if eq( $row.source, '' )} selected="selected"{/if}>{'(leave empty)'|i18n( 'extension/expchangeclass' )}</option>
                {foreach $plan.candidates[$row.identifier] as $candidate}
                <option value="{$candidate.identifier|wash}"{if eq( $row.source, $candidate.identifier )} selected="selected"{/if}>{$candidate.name|wash} ({$candidate.datatype|wash})</option>
                {/foreach}
            </select>
        </td>
        <td>{include uri='design:changeclass/parts/kind.tpl' kind=$row.kind}{if $row.converter}<div class="a4-meta">{$row.converter|wash}</div>{/if}</td>
        {if $show_values}
        <td>{if $row.source}
            <ul class="a4-plain">{foreach $row.values as $language => $value}<li><span class="a4-tag is-mono">{$language|wash}</span> {if $value.has_content}{$value.text|wash}{else}<span class="a4-muted">{'(empty)'|i18n( 'extension/expchangeclass' )}</span>{/if}</li>{/foreach}</ul>
        {else}<span class="a4-muted">&ndash;</span>{/if}</td>
        {/if}
    </tr>
    {/foreach}
    </tbody>
    </table>
    </div>
</section>

<section class="a4-card" aria-labelledby="exp-cc-dropped-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-dropped-title">{'Old attributes nothing takes over'|i18n( 'extension/expchangeclass' )}</h2></div>
    {if $plan.dropped}
    <p class="a4-meta">{'Their content is no longer shown after the change. It is not deleted: the restore point keeps it until it is discarded.'|i18n( 'extension/expchangeclass' )}</p>
    <div class="a4-table-wrap">
    <table class="a4-table">
    <thead><tr>
        <th scope="col">{'Old attribute'|i18n( 'extension/expchangeclass' )}</th>
        <th scope="col">{'Datatype'|i18n( 'extension/expchangeclass' )}</th>
        {if $show_values}<th scope="col">{'Content now'|i18n( 'extension/expchangeclass' )}</th>{/if}
    </tr></thead>
    <tbody>
    {foreach $plan.dropped as $dropped}
    <tr>
        <th scope="row">{$dropped.name|wash} <span class="a4-tag is-mono">{$dropped.identifier|wash}</span></th>
        <td><span class="a4-tag">{$dropped.datatype|wash}</span></td>
        {if $show_values}
        <td>{if $dropped.languages}<ul class="a4-plain">{foreach $dropped.values as $language => $value}{if $value.has_content}<li><span class="a4-tag is-mono">{$language|wash}</span> {$value.text|wash}</li>{/if}{/foreach}</ul>{else}<span class="a4-muted">{'(empty in every language)'|i18n( 'extension/expchangeclass' )}</span>{/if}</td>
        {/if}
    </tr>
    {/foreach}
    </tbody>
    </table>
    </div>
    {else}
    <p class="a4-empty">{'Every old attribute is taken over: nothing is dropped.'|i18n( 'extension/expchangeclass' )}</p>
    {/if}
</section>
