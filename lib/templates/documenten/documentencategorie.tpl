{*
 * Toon een overzicht van documenten in een bepaalde categorie
 *}
<div id="controls">
	{if $loginlid->hasPermission('P_DOCS_MOD')}
		<a class="knop" href="/communicatie/documenten/toevoegen/?catID={$categorie->getID()}">{icon get="toevoegen"} Toevoegen</a>
	{/if}
</div>

<a href="/communicatie/documenten">Documenten</a><h1>{$categorie->getNaam()}</h1>
<div class="foutje">{$melding}</div>

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
				<a href="{$document->getDownloadurl()}">{$document->getNaam()|escape:'html'}</a>
				{if $document->magVerwijderen()}
					<a class="verwijderen" href="/communicatie/documenten/verwijderen/{$document->getID()}" title="Document verwijderen">{icon get="verwijderen"}</a>
				{/if}
			</td>
			<td class="size">{$document->getSize()}</td>
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
