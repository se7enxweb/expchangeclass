{* How a new attribute gets its content: a badge. Parameter: kind (same, simple, converter, copy, empty, ''). *}
{if eq( $kind, 'same' )}<span class="a4-badge is-ok">{'Carried over'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $kind, 'simple' )}<span class="a4-badge is-info">{'Carried over, other datatype'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $kind, 'converter' )}<span class="a4-badge is-info">{'Converted'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $kind, 'copy' )}<span class="a4-badge is-warn">{'Copied'|i18n( 'extension/expchangeclass' )}</span>
{elseif eq( $kind, 'empty' )}<span class="a4-badge">{'Stays empty'|i18n( 'extension/expchangeclass' )}</span>
{else}<span class="a4-badge is-bad">{'Not possible'|i18n( 'extension/expchangeclass' )}</span>{/if}
