{* The message the last POST left (expChangeClassView::flash()): kind (ok, warn, bad, info), text, lines. Parameter: message. *}
{if $message}
<div class="a4-notice is-{$message.kind|wash}" role="{if eq( $message.kind, 'bad' )}alert{else}status{/if}">
    <p><strong>{$message.text|wash}</strong></p>
    {if $message.lines}<ul>{foreach $message.lines as $line}<li>{$line|wash}</li>{/foreach}</ul>{/if}
</div>
{/if}
