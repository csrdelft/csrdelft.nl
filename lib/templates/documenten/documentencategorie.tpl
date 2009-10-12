{*
 * Toon een overzicht van documenten in een bepaalde categorie
 *}
<div id="controls">
	<a class="knop" href="/communicatie/documenten_new/toevoegen/?catID={$categorie->getID()}">Toevoegen</a>
</div>

<h1>{$categorie->getNaam()}</h1>

{if !is_array($categorie->getAll())}
	Geen documenten in deze categorie.
{else}
	<table id="documentencategorie" class="documenten">
		<thead>
			<tr>
				<th>Document</th><th>Bestandsgrootte</th><th>Mime-type</th><th>Toegevoegd</th><th>Eigenaar</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$categorie->getDocumenten() item=document}
			<tr class="document">
				<td><a href="/communicatie/documenten_new/download/{$document->getID()}/{$document->getBestandsnaam()}">{$document->getNaam()|escape:'html'}</a></td>
				<td class="size">{$document->getSize()}</td>
				<td class="mimetype">{$document->getMimetype()}</td>
				<td class="datum">{$document->getToegevoegd()|reldate}</td>
				<td class="eigenaar">{$document->getEigenaar()|csrnaam}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}
