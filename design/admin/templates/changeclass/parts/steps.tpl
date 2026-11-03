{* The steps of a class change: 1 the new class, 2 the mapping and preview, 3 the result. Parameter: step (1, 2, 3). *}
<ol class="a4-steps" aria-label="{'Steps'|i18n( 'extension/expchangeclass' )|wash}">
    <li class="{if gt( $step, 1 )}is-done{elseif eq( $step, 1 )}is-current{/if}"{if eq( $step, 1 )} aria-current="step"{/if}>{'Choose the new class'|i18n( 'extension/expchangeclass' )}</li>
    <li class="{if gt( $step, 2 )}is-done{elseif eq( $step, 2 )}is-current{/if}"{if eq( $step, 2 )} aria-current="step"{/if}>{'Map the attributes and preview'|i18n( 'extension/expchangeclass' )}</li>
    <li class="{if eq( $step, 3 )}is-current{/if}"{if eq( $step, 3 )} aria-current="step"{/if}>{'Result and restore point'|i18n( 'extension/expchangeclass' )}</li>
</ol>
