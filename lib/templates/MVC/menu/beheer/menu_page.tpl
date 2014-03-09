{*
	menu_tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{$view->getMelding()}
<h1>{$view->getTitel()}</h1>
<div style="float: right;">
	<div style="display: inline-block;"><label for="toon">Toon menu:</label>
	</div><select name="toon" onchange="location.href = '/menubeheer/beheer/' + this.value;">
		<option selected="selected">kies</option>
		{foreach from=$menus item=menu}
			<option value="{$menu}">{$menu}</option>
		{/foreach}
	</select>
</div>
<p>
	Op deze pagina kunt u het menu beheren.
</p>
<ul class="menubeheer-tree">
	{if $root AND $root->children}
		{foreach from=$root->children item=child}
			{include file='MVC/menu/beheer/menu_item.tpl' item=$child}
		{/foreach}
	{/if}
</ul>