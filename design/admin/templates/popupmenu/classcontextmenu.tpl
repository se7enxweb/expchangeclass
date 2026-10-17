{* Only for a user who may change classes: every changeclass view needs changeclass/convert *}
{if fetch( 'user', 'has_access_to', hash( 'module', 'changeclass', 'function', 'convert' ) )}
 <hr/>
    <a id="menu-class-change" href="#" data-menu-form="menu-form-class-change">{"Change content class"|i18n("design/admin/changeclass")}</a>


<form id="menu-form-class-change" method="post" action={"/changeclass/action"|ezurl}>
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="ObjectID" value="%objectID%" />
  <input type="hidden" name="SelectSourceObjectButton" value="submit" />
</form>
{* the form of the entry (data-menu-form): ezpopupmenu.js, or expchangeclass_menu.js with an older one; no inline code *}
{ezscript( array( 'expchangeclass_menu.js' ) )}
{/if}
