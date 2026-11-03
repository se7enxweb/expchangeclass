{* What refuses a change (errors) and what an editor should know (warnings), translated by expChangeClassView. Parameters: errors, warnings. *}
{if $errors}
<div class="a4-notice is-bad" role="alert">
    <p><strong>{'The class cannot be changed yet:'|i18n( 'extension/expchangeclass' )}</strong></p>
    <ul>{foreach $errors as $problem}<li>{$problem.text|wash}</li>{/foreach}</ul>
</div>
{/if}
{if $warnings}
<div class="a4-notice is-warn" role="status">
    <p><strong>{'Before you apply the change:'|i18n( 'extension/expchangeclass' )}</strong></p>
    <ul>{foreach $warnings as $problem}<li>{$problem.text|wash}</li>{/foreach}</ul>
</div>
{/if}
