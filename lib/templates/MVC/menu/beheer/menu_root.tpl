{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<a href="/menubeheer/toevoegen/{$root->item_id}" class="knop post modal" title="Menu-item toevoegen">{icon get="add"}</a>
<span class="">{$root->tekst}</span>
<span class="lichtgrijs">{$root->item_id}</span>
<div class="float-right">
	<a href="{$root->link}">{$root->link}</a>
</div>
<hr />