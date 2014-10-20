{*
* Toon een overzicht van documenten in de verschillende categorieën
*}

<div id="controls">
	{if LoginModel::mag('P_DOCS_MOD')}
		<a class="knop" href="/communicatie/documenten/toevoegen/">{icon get="toevoegen"} Toevoegen</a>
	{/if}
</div>
<h1>Documenten</h1>
<div class="foutje">{getMelding()}</div>

<table id="documenten" class="documenten">
	<thead>
		<tr>
			<th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
		</tr>
	</thead>

	{foreach from=$categorieen item=categorie}

		<tbody>
			<tr>
				<th colspan="5">
					<a href="/communicatie/documenten/categorie/{$categorie->getID()}/" title="Alle documenten in {$categorie->getNaam()|escape:'html'}">
						{$categorie->getNaam()|escape:'html'}
					</a>
					{if LoginModel::mag('P_DOCS_MOD')}
						<a class="toevoegen" href="/communicatie/documenten/toevoegen/?catID={$categorie->getID()}"
						   title="Document toevoegen in categorie: {$categorie->getNaam()|escape:'html'}">
							{icon get="toevoegen"}
						</a>
					{/if}
				</th>
			</tr>
			{foreach from=$categorie->getLast(5) item=document}
				<tr class="document">
					<td>
						{if $document->hasFile()}
							<a href="{$document->getDownloadurl()}">
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
					<td class="size">{$document->getFileSize()|filesize}</td>
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
			<th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th>
		</tr>
	</tfoot>
</table>
