{$melding}
<div class="groepleden">
	<strong>leden:</strong>
	<table>
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				<td><em>{$groeplid.functie|escape:'html'}</em></td>
				{if $groep->magBewerken()}
					<td style="display: block;"><a href="/groepen/{$groep->getId()}/verwijder/lid/{$groeplid.uid}"><img src="{$csr_pics}forum/verwijderen.png" title="Verwijder lid" /></a></td>
				{/if}
			</tr>
		{/foreach}
	</table>
</div>
<h2>{$groep->getNaam()}</h2>
{if $groep->magBewerken() AND $action=='edit'}
	<form action="/groepen/{$groep->getId()}/bewerken" method="post">
	<div class="groepAdmin" style="width: 100%; clear: both;">
		<h2>Commissie bewerken:</h2>
		<strong>Korte beschrijving:</strong><br />
		<textarea name="sbeschrijving" style="width: 100%">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		<strong>Lange beschrijving:</strong><br />
		<textarea name="beschrijving" style="width: 100%; height: 200px;">{$groep->getBeschrijving|escape:'html'}</textarea>
		<input type="submit" value="Opslaan" /> <a href="/groepen/{$groep.getId}/" class="knop">terug</a>
	</div>
	</form>
{else}
	{if $groep->magBewerken()}
		<div style="float: right; margin: 10px ;"><a href="/groepen/{$groep->getId()}/bewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk groep" /></a></div>
	{/if}
	{$groep->getBeschrijving()|ubb}
{/if}