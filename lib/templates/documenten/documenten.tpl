{*
 * Toon een overzicht van documenten in de verschillende categorieën
 *}

<div id="controls">
	<a class="knop" href="/communicatie/documenten_new/toevoegen/">Toevoegen</a>
</div>
<h1>Documenten</h1>

{foreach from=$categorieen item=categorie}
	<h2>{$categorie->getNaam()}</h2>
	{foreach from=$categorie->getLast(5) item=document}

	{foreachelse}
		<li>Geen documenten in deze categorie</li>
	{/foreach}
{foreachelse}
	Geen categorieën in de database aanwezig.
{/foreach}
