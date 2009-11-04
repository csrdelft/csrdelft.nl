{*
 * Toon een overzicht van documenten in de verschillende categorieën
 *}

<div id="controls">
	<a class="knop" href="/communicatie/documenten/toevoegen/">{icon get="toevoegen"} Toevoegen</a>
</div>
<h1>Documenten</h1>
<div class="foutje">{$melding}</div>

<table id="documenten" class="documenten">
<thead>
	<tr>
		<th>Document</th><th>Bestandsgrootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
	</tr>
</thead>

{foreach from=$categorieen item=categorie}
	
	<tbody>
		<tr>
			<th colspan="5">
				<a href="/communicatie/documenten/categorie/{$categorie->getID()}/" title="Alle documenten in {$categorie->getNaam()|escape:'html'}">
					{$categorie->getNaam()|escape:'html'}
				</a>
				<a class="toevoegen" href="/communicatie/documenten/toevoegen/?catID={$categorie->getID()}"
						title="Document toevoegen in categorie: {$categorie->getNaam()|escape:'html'}">
					{icon get="toevoegen"}
				</a>
			</th>
		</tr>
		{foreach from=$categorie->getLast(5) item=document}
			<tr class="document">
				<td><a href="{$document->getDownloadurl()}">{$document->getNaam()|escape:'html'}</a></td>
				<td class="size">{$document->getSize()|filesize}</td>
				<td title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</td>
				<td>{$document->getToegevoegd()|reldate}</td>
				<td>{$document->getEigenaar()|csrnaam}</td>
			</tr>
		{foreachelse}
			<tr><td class="document" colspan="5">Geen documenten in deze categorie</td></tr>
		{/foreach}
	</tbody>
{foreachelse}
	<tr><td colspan="5">Geen categorieën in de database aanwezig.</td></tr>
{/foreach}
	<tfoot>
		<tr>
			<th>Document</th><th>Bestandsgrootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
		</tr>
	</tfoot>
</table>
