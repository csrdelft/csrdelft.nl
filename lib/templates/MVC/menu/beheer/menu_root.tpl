{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if LoginModel::mag('P_ADMIN')}
	<a href="/menubeheer/bewerken/{$root->item_id}" class="knop post modal" title="Dit menu bewerken">{icon get="bewerken"}</a>
{/if}
<a href="/menubeheer/toevoegen/{$root->item_id}" class="knop post modal" title="Menu-item toevoegen">{icon get="add"}</a>
<span>{if $root->tekst == LoginModel::getUid()}Favorieten{else}{$root->tekst}{/if}</span>
{if LoginModel::mag('P_ADMIN')}
	<span class="lichtgrijs">{$root->item_id}</span>
{/if}
<hr />