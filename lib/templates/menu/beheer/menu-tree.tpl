{*
	menu-tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<h1>{$kop}</h1>
<p>
Op deze pagina kunt u het menu beheren.
</p>
<div style="display: inline-block;"><label for="toon">Toon menu:</label>
</div><select name="toon" onchange="location.href='/menubeheer/'+this.value;">
		<option selected="selected">kies</option>
	{foreach from=$menus item=menu}
		<option value="{$menu->getTekst()}">{$menu->getTekst()}</option>
	{/foreach}
</select>
{foreach from=$root->getChildren() item=child}
	{include file='menu/beheer/menu-item.tpl' item=$child}
{/foreach}