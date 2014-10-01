{*
	menu_tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{SimpleHtml::getMelding()}
<div class="float-right">
	<div class="inline"><label for="toon">Toon menu:</label>
	</div><select name="toon" onchange="location.href = '/menubeheer/beheer/' + this.value;">
		<option selected="selected">kies</option>
		{foreach from=$menus item=menu}
			<option value="{$menu}">{$menu}</option>
		{/foreach}
	</select>
</div>
<h1 style="width: 650px;">{$titel}</h1>
<br />
<ul class="menubeheer-tree">
	{if $root}
		<li>{include file='MVC/menu/beheer/menu_root.tpl'}</li>
		{if $root->children}
			{foreach from=$root->children item=child}
				{include file='MVC/menu/beheer/menu_item.tpl' item=$child}
			{/foreach}
		{/if}
	{/if}
</ul>