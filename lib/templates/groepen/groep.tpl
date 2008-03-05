{$melding}
<div class="groepleden">
	<strong>leden:</strong>
	<table>
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				<td><em>{$groeplid.functie|escape:'html'}</em></td>
				{if $groep->magBewerken()}
					<td><a href="/groepen/{$gtype}/{$groep->getId()}/verwijder/lid/{$groeplid.uid}">X</a></td>
				{/if}
			</tr>
		{/foreach}
	</table>
</div>
<h2>{$groep->getNaam()}</h2>
{if $groep->magBewerken() AND $action=='edit'}
	<form action="/groepen/{$gtype}/{$groep->getId()}/bewerken" method="post">
	<div class="groepAdmin" style="width: 100%; clear: both;">
		<h2>Groep bewerken:</h2>
		{if $groep->isAdmin()}
		<strong>Naam:</strong><br />
		<input type="text" name="naam" style="width: 100%" value="{$groep->getNaam()|escape:'html'}" />
		<strong>Korte beschrijving:</strong><br />
		<textarea name="sbeschrijving" style="width: 100%">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		{/if}
		<strong>Lange beschrijving:</strong><br />
		<textarea name="beschrijving" style="width: 100%; height: 200px;">{$groep->getBeschrijving()|escape:'html'}</textarea>
		<input type="submit" value="Opslaan" /> <a href="/groepen/{$gtype}/{$groep->getId()}/" class="knop">terug</a>
	</div>
	</form>
{else}
	<strong>{$groep->getSbeschrijving()|ubb}</strong>
	<hr class="clear" /> 
	{if $groep->magBewerken()}
		<div style="float: right; margin: 10px ;"><a href="/groepen/{$gtype}/{$groep->getId()}/bewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk groep" /></a></div>
	{/if}
	
	{$groep->getBeschrijving()|ubb}
{/if}