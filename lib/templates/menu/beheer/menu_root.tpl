{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if LoginModel::mag('P_ADMIN')}
	<a href="/menubeheer/bewerken/{$root->id}" class="btn post popup" title="Dit menu bewerken">{icon get="bewerken"}</a>
{/if}
<a href="/menubeheer/toevoegen/{$root->id}" class="btn post popup" title="Menu-item toevoegen">{icon get="add"}</a>
<span>{if $root->tekst == LoginModel::getUid()}Favorieten{else}{$root->tekst}{/if}</span>
{if LoginModel::mag('P_ADMIN')}
	<span class="lichtgrijs">{$root->id}</span>
{/if}
<hr />