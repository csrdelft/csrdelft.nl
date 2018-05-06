{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{toegang P_ADMIN}
	<a href="/menubeheer/bewerken/{$root->item_id}" class="btn post popup" title="Dit menu bewerken">{icon get="bewerken"}</a>
{/toegang}
<a href="/menubeheer/toevoegen/{$root->item_id}" class="btn post popup" title="Menu-item toevoegen">{icon get="add"}</a>
<span>{if $root->tekst == CsrDelft\model\security\LoginModel::getUid()}Favorieten{else}{$root->tekst}{/if}</span>
{toegang P_ADMIN}
	<span class="lichtgrijs">{$root->item_id}</span>
{/toegang}
<hr />
