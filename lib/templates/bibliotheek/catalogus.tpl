{*
 * Toon de catalogus
 *}
{*<div id="controls">
	{if $loginlid->hasPermission('P_BIEB_MOD')}
		<a class="knop" href="/communicatie/bibliotheek/toevoegen/">{icon get="toevoegen"} Toevoegen</a>
	{/if}
</div>*}

<h1>C.S.R. Bibliotheekcatalogus</h1>
<div class="foutje">{$melding}</div>

{if !$catalogus->count()>0}
	Geen boeken.
{else}
<table id="boekencatalogus" class="boeken">
	<thead>
		<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th></tr>
	</thead>
	<tbody>
	{foreach from=$catalogus->getBoeken() item=boek}
		<tr class="document">
			<td>
				<a href="/communicatie/bibliotheek/boek/{$boek->getID()}" title="Boek bekijken">
					{$boek->getTitel()|escape:'html'|wordwrap:60:'<br />'}
				</a>
				{if $boek->magVerwijderen()}
					<a class="verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getID()}" title="Boek verwijderen" onclick="confirm('Weet u zeker dat u dit boek wilt verwijderen')">{icon get="verwijderen"}</a>
				{/if}
			</td>
			<td class="auteur">{$boek->getAuteur()|wordwrap:50:'<br />'}</td>
			<td class="rubriek">{$boek->getRubriek()|wordwrap:70:'<br />'}</td>
			<td class="code">{$boek->getCode()}</td>
			<td class="isbn">{$boek->getISBN()}</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th></tr>
	</tfoot>
</table>

{/if}

