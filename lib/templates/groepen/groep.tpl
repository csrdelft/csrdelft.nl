{$melding}
<ul class="horizontal">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groep->getTypeId()}<strong>{/if}
			<a href="/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groep->getTypeId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
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
		<h2>Groep {if $groep->getId()==0}toevoegen{else}bewerken{/if}:</h2>
		{if $groep->isAdmin()}
			{if $groep->getId()==0}
				<strong>Korte naam:</strong> (Voor in urls. Alleen letters, geen spaties.)<br />
				<input type="text" name="snaam" style="width: 100%" value="{$groep->getSnaam()|escape:'html'}" />
			{/if}
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