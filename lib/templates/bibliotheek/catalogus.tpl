{*
 * Toon de catalogus
 *}
<ul class="horizontal">
	<li class="active">
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li>
		<a href="/communicatie/bibliotheek/boekstatus" title="Uitgebreide boekstatus">Boekstatus</a>
	</li>
</ul>
{if $loginlid->hasPermission('P_BIEB_READ')}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek">{icon get="book_add"} Toevoegen</a>
	</div>
{/if}

<h1>Catalogus van de C.S.R.-bibliotheek</h1>
<div class="foutje">{$melding}</div>
<div id="filters">
	Selecteer: <a {if $catalogus->getFilter()=='alle'}class="actief"{/if} href="/communicatie/bibliotheek/alle">Alle boeken</a> - 
	<a {if $catalogus->getFilter()=='csr'}class="actief"{/if} href="/communicatie/bibliotheek/csr">C.S.R.-boeken</a> - 
	<a {if $catalogus->getFilter()=='leden'}class="actief"{/if} href="/communicatie/bibliotheek/leden">Boeken van Leden</a>
</div>


<table id="boekencatalogus" class="boeken">
	<thead>
		<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th></tr>
	</thead>
	<tbody>
	{foreach from=$catalogus->getBoeken(true) item=boek}
		<tr class="document">
			<td>
				<span title="{$boek->getStatus()} boek" class="indicator {$boek->getStatus()}">•</span><a href="/communicatie/bibliotheek/boek/{$boek->getId()}" title="Boek bekijken">
					{$boek->getTitel()|escape:'html'|wordwrap:60:'<br />'}
				</a>
				{*{if $boek->magVerwijderen()}
					<a class="verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getId()}" title="Boek verwijderen" onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen')">{icon get="verwijderen"}</a>
				{/if}*}
			</td>
			<td class="auteur">{$boek->getAuteur()->getNaam()|wordwrap:50:'<br />'}</td>
			<td class="rubriek">{$boek->getRubriek()->getRubrieken()|wordwrap:70:'<br />'}</td>
			<td class="code">{$boek->getCode()}</td>
			<td class="isbn">{$boek->getISBN()}</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th></tr>
	</tfoot>
</table>


