{* Toon een overzicht van documenten in een bepaalde categorie *}

<div id="controls">
	{if LoginModel::mag('P_DOCS_MOD')}
		<a class="btn" href="/communicatie/documenten/toevoegen/?catID={$categorie->getID()}">{icon get="toevoegen"} Toevoegen</a>
	{/if}
</div>

{getMelding()}

<h1>{$categorie->getNaam()}</h1>

{if !is_array($categorie->getAll())}
	Geen documenten in deze categorie.
{else}
<table id="documentencategorie" class="documenten">
	<thead>
		<tr><th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th></tr>
	</thead>
	<tbody>
	{foreach from=$categorie->getDocumenten() item=document}
		<tr class="document">
			<td>
				{if $document->hasFile()}
					<a href="{$document->getUrl()}" target="_blank">
				{else}
					<a title="Bestand niet gevonden..." class="filenotfound">
				{/if}
				{$document->getNaam()|escape:'html'|wordwrap:70:'<br />'}
				</a>
				{if $document->magVerwijderen()}
					<a class="verwijderen" href="/communicatie/documenten/verwijderen/{$document->getID()}" title="Document verwijderen" onclick="return confirm('Weet u zeker dat u dit document wilt verwijderen')">{icon get="verwijderen"}</a>
				{/if}
				{if $document->magBewerken()}
					<a class="bewerken" href="/communicatie/documenten/bewerken/{$document->getID()}" title="Document bewerken">{icon get="bewerken"}</a>
				{/if}
			</td>
			<td class="size">{$document->getFileSize()}</td>
			<td class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</td>
			<td class="datum"><div class="verborgen">{$document->getToegevoegd()}</div>{$document->getToegevoegd()|reldate}</td>
			<td class="eigenaar">{$document->getEigenaar()|csrnaam}</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th></tr>
	</tfoot>
</table>

{/if}
