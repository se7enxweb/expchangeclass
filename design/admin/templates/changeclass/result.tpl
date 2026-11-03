{* changeclass/result/<RestorePointID>: step 3 of a class change, what was done and the restore point, in every admin
   design. Only shared components and tokens. Variables: restore_point (expChangeClassRestorePoint::summary()),
   object, node, restore_problem, can_restore, can_discard, command, bulk_count, can_bulk, message. *}
{def $point = $restore_point}
<div class="context-block a4-page">

<div class="box-header"><div class="box-ml">
<h1 class="context-title"><span class="a4-icon a4-bi-diagram-3 is-large" aria-hidden="true"></span> {'Class change of %name'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name ) )|wash}</h1>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

{include uri='design:changeclass/parts/steps.tpl' step=3}

{include uri='design:changeclass/parts/message.tpl' message=$message}

{if eq( $point.state, 'applied' )}
<div class="a4-notice is-ok" role="status"><p>{'%name is of the class %class now. Every version and language was changed in one transaction.'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name, '%class', $point.dest_class.name ) )|wash}</p></div>
{elseif eq( $point.state, 'failed' )}
<div class="a4-notice is-bad" role="alert"><p>{'The change was rolled back: nothing was changed, the content is exactly as it was.'|i18n( 'extension/expchangeclass' )}</p>{if $point.error}<p>{$point.error|wash}</p>{/if}</div>
{elseif eq( $point.state, 'restored' )}
<div class="a4-notice is-info" role="status"><p>{'This change was undone: %name is of the class %class again.'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name, '%class', $point.source_class.name ) )|wash}</p></div>
{elseif eq( $point.state, 'discarded' )}
<div class="a4-notice is-info" role="status"><p>{'The restore point of this change was discarded; the change can no longer be undone from here.'|i18n( 'extension/expchangeclass' )}</p></div>
{else}
<div class="a4-notice is-warn" role="status"><p>{'This change has not finished. If it does not finish within a minute, it was interrupted before it was committed and nothing was changed.'|i18n( 'extension/expchangeclass' )}</p></div>
{/if}

<div class="a4-stack">

<section class="a4-card" aria-labelledby="exp-cc-done-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-done-title">{'The change'|i18n( 'extension/expchangeclass' )}</h2></div>
    <dl class="a4-facts">
        <div><dt>{'Object'|i18n( 'extension/expchangeclass' )}</dt><dd>{if $node}<a href={$node.url_alias|ezurl}>{$point.object.name|wash}</a>{else}{$point.object.name|wash}{/if} <span class="a4-meta">{'Object ID %id'|i18n( 'extension/expchangeclass',, hash( '%id', $point.object.id ) )|wash}</span></dd></div>
        <div><dt>{'From'|i18n( 'extension/expchangeclass' )}</dt><dd>{$point.source_class.name|wash} <span class="a4-tag is-mono">{$point.source_class.identifier|wash}</span></dd></div>
        <div><dt>{'Into'|i18n( 'extension/expchangeclass' )}</dt><dd>{$point.dest_class.name|wash} <span class="a4-tag is-mono">{$point.dest_class.identifier|wash}</span></dd></div>
        <div><dt>{'When'|i18n( 'extension/expchangeclass' )}</dt><dd>{$point.created|l10n( 'shortdatetime' )}</dd></div>
        <div><dt>{'By'|i18n( 'extension/expchangeclass' )}</dt><dd>{$point.user_login|wash} <span class="a4-meta">{$point.via|wash}</span></dd></div>
        <div><dt>{'State'|i18n( 'extension/expchangeclass' )}</dt><dd>{include uri='design:changeclass/parts/state.tpl' state=$point.state}</dd></div>
        <div><dt>{'Versions'|i18n( 'extension/expchangeclass' )}</dt><dd>{$point.versions|count}</dd></div>
        <div><dt>{'Restore point'|i18n( 'extension/expchangeclass' )}</dt><dd><span class="a4-tag is-mono">{$point.id|wash}</span>{if $point.attribute_rows}<span class="a4-meta">{'%rows attribute rows kept'|i18n( 'extension/expchangeclass',, hash( '%rows', $point.attribute_rows ) )|wash}</span>{/if}</dd></div>
    </dl>
</section>

<section class="a4-card" aria-labelledby="exp-cc-map-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-map-title">{'Mapping used'|i18n( 'extension/expchangeclass' )}</h2></div>
    <div class="a4-table-wrap">
    <table class="a4-table">
    <thead><tr><th scope="col">{'New attribute'|i18n( 'extension/expchangeclass' )}</th><th scope="col">{'Took its content from'|i18n( 'extension/expchangeclass' )}</th></tr></thead>
    <tbody>
    {foreach $point.mapping as $to => $from}
    <tr><th scope="row"><span class="a4-tag is-mono">{$to|wash}</span></th><td>{if $from}<span class="a4-tag is-mono">{$from|wash}</span>{else}<span class="a4-muted">{'(empty)'|i18n( 'extension/expchangeclass' )}</span>{/if}</td></tr>
    {/foreach}
    </tbody>
    </table>
    </div>
</section>

{if or( $can_restore, $can_discard, $restore_problem )}
<section class="a4-card" aria-labelledby="exp-cc-undo-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-undo-title">{'Undo'|i18n( 'extension/expchangeclass' )}</h2></div>
    {if $can_restore}
    <p>{'Restoring puts every attribute row of every version and language back as it was before the change, and the class %class with it, in one transaction.'|i18n( 'extension/expchangeclass',, hash( '%class', $point.source_class.name ) )|wash}</p>
    {elseif and( eq( $point.state, 'applied' ), $restore_problem )}
    <div class="a4-notice is-warn" role="status"><p>{$restore_problem|wash}</p></div>
    {/if}
    <form method="post" action={concat( 'changeclass/result/', $point.id )|ezurl}>
    <div class="a4-actions">
        {if $can_restore}
        <details class="a4-confirm">
            <summary>{'Restore the object…'|i18n( 'extension/expchangeclass' )}</summary>
            <div><p>{'%name becomes %class again, with its attributes as they were.'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name, '%class', $point.source_class.name ) )|wash}</p>
            <button class="a4-btn a4-btn-primary" type="submit" name="RestoreButton" value="1">{'Restore'|i18n( 'extension/expchangeclass' )}</button></div>
        </details>
        {/if}
        {if $can_discard}
        <details class="a4-confirm">
            <summary>{'Discard the restore point…'|i18n( 'extension/expchangeclass' )}</summary>
            <div><p>{'The content of the dropped attributes (and their files) is deleted for good, and the change can no longer be undone.'|i18n( 'extension/expchangeclass' )}</p>
            <button class="a4-btn a4-btn-danger" type="submit" name="DiscardButton" value="1">{'Discard'|i18n( 'extension/expchangeclass' )}</button></div>
        </details>
        {/if}
    </div>
    </form>
</section>
{/if}

<section class="a4-card" aria-labelledby="exp-cc-next-title">
    <div class="a4-card-head"><h2 class="a4-h3" id="exp-cc-next-title">{'Next'|i18n( 'extension/expchangeclass' )}</h2></div>
    <ul class="a4-plain">
        {if $node}<li><a href={$node.url_alias|ezurl}>{'Open %name'|i18n( 'extension/expchangeclass',, hash( '%name', $point.object.name ) )|wash}</a></li>{/if}
        {if and( $can_bulk, gt( $bulk_count, 0 ), eq( $point.state, 'applied' ) )}<li><a href={concat( 'changeclass/bulk/', $point.source_class.id, '/', $point.dest_class.id, '/2' )|ezurl}>{'Change the other %count objects of %class the same way, in the background'|i18n( 'extension/expchangeclass',, hash( '%count', $bulk_count, '%class', $point.source_class.name ) )|wash}</a></li>{/if}
        <li><a href={'changeclass/dashboard'|ezurl}>{'All class changes'|i18n( 'extension/expchangeclass' )}</a></li>
    </ul>
    {if eq( $point.state, 'applied' )}<p class="a4-meta">{'From a shell:'|i18n( 'extension/expchangeclass' )} <code>{$command|wash}</code></p>{/if}
</section>

</div>

</div></div></div>

</div>
{undef $point}
