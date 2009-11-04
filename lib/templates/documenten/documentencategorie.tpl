{*
 * Toon een overzicht van documenten in een bepaalde categorie
 *}
<div id="controls">
	<a class="knop" href="/communicatie/documenten_new/toevoegen/?catID={$categorie->getID()}">Toevoegen</a>
</div>

<a href="/communicatie/documenten_new">Documenten</a><h1>{$categorie->getNaam()}</h1>
<div class="foutje">{$melding}</div>

{if !is_array($categorie->getAll())}
	Geen documenten in deze categorie.
{else}
	<table id="documentencategorie" class="documenten">
		<thead>
			<tr>
				<th>Document</th><th>Bestandsgrootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$categorie->getDocumenten() item=document}
			<tr class="document">
				<td><a href="{$document->getDownloadurl()}">{$document->getNaam()|escape:'html'}</a></td>
				<td class="size">{$document->getSize()}</td>
				<td class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</td>
				<td class="datum"><div class="verborgen">{$document->getToegevoegd()}</div>{$document->getToegevoegd()|reldate}</td>
				<td class="eigenaar">{$document->getEigenaar()|csrnaam}</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<th>Document</th><th>Bestandsgrootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
			</tr>
		</tfoot>
	</table>
	
{/if}
