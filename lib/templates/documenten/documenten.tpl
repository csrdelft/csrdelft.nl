{* Toon een overzicht van documenten in de verschillende categorieën *}

<div id="controls">
	{toegang P_DOCS_MOD}
		<a class="btn" href="/documenten/toevoegen">{icon get="toevoegen"} Toevoegen</a>
	{/toegang}
</div>

{getMelding()}

<h1>Documenten</h1>

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
					<a href="/documenten/categorie/{$categorie->id}/" title="Alle documenten in {$categorie->naam|escape:'html'}">
						{$categorie->naam|escape:'html'}
					</a>
					{toegang P_DOCS_MOD}
						<a class="toevoegen" href="/documenten/toevoegen/?catID={$categorie->id}"
						   title="Document toevoegen in categorie: {$categorie->naam|escape:'html'}">
							{icon get="toevoegen"}
						</a>
					{/toegang}
				</th>
			</tr>
			{foreach from=$model->getRecent($categorie, 5) item=document}
				<tr class="document">
					<td>
						{if $document->hasFile()}
							<a href="{$document->getUrl()}" target="_blank">
							{else}
								<a title="Bestand niet gevonden..." class="filenotfound">
								{/if}
								{$document->naam|escape:'html'|wordwrap:70:'<br />'}
							</a>

							{if $document->magVerwijderen()}
								<a class="verwijderen" href="/documenten/verwijderen/{$document->id}" title="Document verwijderen" onclick="return confirm('Weet u zeker dat u dit document wilt verwijderen')">{icon get="verwijderen"}</a>
							{/if}
							{if $document->magBewerken()}
								<a class="bewerken" href="/documenten/bewerken/{$document->id}" title="Document bewerken">{icon get="bewerken"}</a>
							{/if}
					</td>
					<td class="size">{$document->filesize|filesize}</td>
					<td title="{$document->mimetype}">{$document->getMimetypeIcon()}</td>
					<td>{$document->toegevoegd|reldate}</td>
					<td>{CsrDelft\model\ProfielModel::getLink($document->eigenaar, 'civitas')}</td>
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
