{* Toon een overzicht van documenten in een bepaalde categorie *}

<div id="controls">
	{toegang P_DOCS_MOD}
		<a class="btn" href="/documenten/toevoegen/?catID={$categorie->id}">{icon get="toevoegen"} Toevoegen</a>
	{/toegang}
</div>

{getMelding()}

<h1>{$categorie->naam}</h1>

<table id="documentencategorie" class="documenten">
	<thead>
		<tr><th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th></tr>
	</thead>
	<tbody>
	{foreach from=$documenten item=document}
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
			<td class="mimetype" title="{$document->mimetype}">{$document->getMimetypeIcon()}</td>
			<td class="datum"><div class="verborgen">{$document->toegevoegd}</div>{$document->toegevoegd|reldate}</td>
			<td class="eigenaar">{CsrDelft\model\ProfielModel::getLink($document->eigenaar, 'civitas')}</td>
		</tr>
	{foreachelse}
		<tr><td class="document" colspan="5">Geen documenten in deze categorie.</td></tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Document</th><th>Grootte</th><th>Type</th><th>Toegevoegd</th><th>Eigenaar</th></tr>
	</tfoot>
</table>
