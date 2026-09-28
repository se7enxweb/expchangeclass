{def $node=fetch( 'content', 'node', hash( 'node_id', $source_node_id ) )
     $source_class_attributes=fetch( 'class', 'attribute_list', hash( 'class_id', $source_class_id ) )
     $classes=fetch( 'class', 'list' )
     
}


<form action={"changeclass/map_attributes"|ezurl} method="post" >
<input type="hidden" name="SourceObjectID" value="{$source_object_id}" />
<input type="hidden" name="SourceNodeID" value="{$source_node_id}" />

<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Change object content class &lt;%name&gt; [%class_name]'|i18n( 'design/admin/changeclass/select_class',, hash( '%name', $node.name|wash(), '%class_name', $node.class_name ) )}</h1>

<div class="header-mainline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

    <table class="list" cellspacing="0">
        <tr>
            <th><label>{'General object\'s informations'|i18n( 'design/admin/changeclass/select_class' )}</label></th>
        </tr>
        <tr>
            <td>

    <div class="block">
        <label>{'Content class name:'|i18n( 'design/admin/changeclass/select_class' )}</label>
        {$node.class_name}
    </div>

    <div class="block">
        <label>{'Children count:'|i18n( 'design/admin/changeclass/select_class' )}</label>
        {$node.children_count}
    </div>

    <div class="block">
        <label>{'Languages:'|i18n( 'design/admin/changeclass/select_class' )}</label>
        {foreach $node.object.languages as $lang}
            {delimiter}&nbsp;{/delimiter}
            <img src="{$lang.locale|flag_icon}" alt="{$lang.language_code|wash}" />&nbsp;
        {/foreach}
    </div>
    {*
    <div class="block">
        <label>Location:</label>
        {$node.main_node_id}
    </div>
      *}
    <div class="block">
        <label>{'Current version:'|i18n( 'design/admin/changeclass/select_class' )}</label>
        {$node.contentobject_version}
    </div>

    <div class="block">
        {def $related_objects=fetch( 'content', 'related_objects', hash( 'object_id', $node.contentobject_id ) )}
        <label>{'Related objects [%count]:'|i18n( 'design/admin/changeclass/select_class',, hash( '%count', $related_objects|count() ) )}</label>
        {if $related_objects}
            {foreach $related_objects as $related_object}
                {$related_object.name}{delimiter}, {/delimiter}
            {/foreach}
        {else}
            {'no objects'|i18n( 'design/admin/changeclass/select_class' )}
        {/if}
    </div>

    <div class="block">
        {def $reverse_related_objects=fetch( 'content', 'reverse_related_objects', hash( 'object_id', $node.contentobject_id ) )}
        <label>{'Reverse related objects [%count]:'|i18n( 'design/admin/changeclass/select_class',, hash( '%count', $reverse_related_objects|count() ) )}</label>
        {if $reverse_related_objects}
            {foreach $reverse_related_objects as $reverse_related_object}
                {$reverse_related_object.name}{delimiter}, {/delimiter}
            {/foreach}
        {else}
            {'no objects'|i18n( 'design/admin/changeclass/select_class' )}
        {/if}
    </div>

    <div class="block">
        <label>{'Attributes (datatype):'|i18n( 'design/admin/changeclass/select_class' )}</label>
        {foreach $source_class_attributes as $attribute}

            {$attribute.name} ({$attribute.data_type_string}) <br />
        {/foreach}
    </div>


            </td>
        </tr>
    </table>
</div>

</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">

    <label>{'Destination content class:'|i18n( 'design/admin/changeclass/select_class' )}</label>
    <br />
    {* A class whose serialized_name_list has no value for any of the siteaccess
       languages resolves to an empty name, which rendered a row that could be
       selected but read as blank. Fall back to the identifier so every row is
       always identifiable, and never offer the class the object already is. *}
    <select name="DestinationClassID">
    {foreach $classes as $class}
        {if ne( $class.id, $source_class_id )}
        <option value="{$class.id}">{if $class.name|trim|ne('')}{$class.name|wash}{else}{'%identifier (unnamed)'|i18n( 'design/admin/changeclass/select_class',, hash( '%identifier', $class.identifier|wash ) )}{/if}</option>
        {/if}
    {/foreach}

</select>

<input class="button" type="submit" name="SelectDestinationClassButton" value="{'Select'|i18n( 'design/admin/changeclass/select_class' )}" />

</div>
</div></div></div></div></div></div>
</div>

</div>
</form>

{*$node|attribute(show)*}
