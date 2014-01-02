{*
	menu-tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<h1>{$kop}</h1>
<div style="float: right;">
	<div style="display: inline-block;"><label for="toon">Toon menu:</label>
	</div><select name="toon" onchange="location.href='/menubeheer/beheer/'+this.value;">
			<option selected="selected">kies</option>
		{foreach from=$menus item=menu}
			<option value="{$menu->getMenu()}">{$menu->getMenu()}</option>
		{/foreach}
	</select>
</div>
<p>
Op deze pagina kunt u het menu beheren.
</p>
<ul class="menubeheer-tree">
	{include file='menu/beheer/menu-item.tpl' item=$tree}
</ul>