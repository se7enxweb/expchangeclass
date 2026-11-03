{* The state of a restore point as a badge. Parameter: state. *}
{if eq( $state, 'applied' )}<span class="a4-badge is-ok">{'Changed, restorable'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $state, 'restored' )}<span class="a4-badge is-info">{'Undone'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $state, 'discarded' )}<span class="a4-badge">{'Restore point discarded'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $state, 'failed' )}<span class="a4-badge is-bad">{'Rolled back'|i18n( 'extension/expchangeclass' )}</span>
{else}<span class="a4-badge is-warn">{'Running'|i18n( 'extension/expchangeclass' )}</span>{/if}
