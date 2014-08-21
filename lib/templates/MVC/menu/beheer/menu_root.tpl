{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<a href="/menubeheer/bewerken/{$root->item_id}" class="knop post popup" title="Naam van dit menu bewerken">{icon get="bewerken"}</a>
<a href="/menubeheer/toevoegen/{$root->item_id}" class="knop post popup" title="Menu-item toevoegen">{icon get="add"}</a>
<span style="font-style: italic;">{$root->tekst}</span>
<span style="color: grey;">{$root->item_id}</span>
<div style="float: right;">
	<a href="{$root->link}">{$root->link}</a>
{if !$root->children}
	<a href="/menubeheer/verwijderen/{$root->item_id}" title="Dit menu definitief verwijderen" class="knop post confirm ReloadPage">{icon get="cross"}</a>
{/if}
</div>
<hr />
</li>