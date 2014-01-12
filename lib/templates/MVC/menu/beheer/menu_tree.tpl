{*
	menu_tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{$melding}
<h1>{$kop}</h1>
<p>
Op deze pagina kunt u het menu beheren.
</p>
<div style="float: right;">
	<div style="display: inline-block;"><label for="toon">Toon menu:</label>
	</div><select name="toon" onchange="location.href='/menubeheer/beheer/'+this.value;">
			<option selected="selected">kies</option>
		{foreach from=$menus item=menu}
			<option value="{$menu->getMenu()}">{$menu->getMenu()}</option>
		{/foreach}
	</select>
	<a href="/menubeheer/beheer/" title="Nieuw menu" class="knop" onclick="this.href+=prompt('Voer unieke naam in','');">{icon get="add"}</a>
</div>
<br />
{if sizeof($root->children) > 0}
<ul class="menubeheer-tree">
	{include file='MVC/menu/beheer/menu_item.tpl' item=$root}
</ul>
{/if}