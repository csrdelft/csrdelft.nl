{*
menu_tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{getMelding()}
{if $menus}
	<div class="float-right">
		<label>Toon menu:</label>
		<select name="toon" onchange="location.href = '/menubeheer/beheer/' + this.value;">
			<option selected="selected">kies</option>
			{foreach from=$menus item=item}
				<option value="{$item->tekst}">{$item->tekst}</option>
			{/foreach}
		</select>
	</div>
{/if}
<h1>Menubeheer</h1>
<ul class="menubeheer-tree">
	{if $root}
		<li>
			{include file='menu/beheer/menu_root.tpl'}
		</li>
		{if $root->children}
			{foreach from=$root->children item=child}
				{include file='menu/beheer/menu_item.tpl' item=$child}
			{/foreach}
		{/if}
	{/if}
</ul>
