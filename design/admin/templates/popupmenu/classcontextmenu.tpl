{* Only for a user who may change classes: every changeclass view needs changeclass/convert *}
{if fetch( 'user', 'has_access_to', hash( 'module', 'changeclass', 'function', 'convert' ) )}
 <hr/>
    <a id="menu-class-change" href="#" onmouseover="ezpopmenu_mouseOver( 'ContextMenu' )"
       onclick="ezpopmenu_submitForm( 'menu-form-class-change' ); return false;">{"Change content class"|i18n("design/admin/changeclass")}</a>


<form id="menu-form-class-change" method="post" action={"/changeclass/action"|ezurl}>
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="ObjectID" value="%objectID%" />
  <input type="hidden" name="SelectSourceObjectButton" value="submit" />
</form>
{/if}
