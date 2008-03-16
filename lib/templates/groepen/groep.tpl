{$melding}
<ul class="horizontal nobullets">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groep->getTypeId()}<strong>{/if}
			<a href="/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groep->getTypeId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
<div id="groepleden">
	<table>
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				<td><em>{$groeplid.functie|escape:'html'}</em></td>
				{if $groep->magBewerken()}
					<td><a href="/groepen/{$gtype}/{$groep->getId()}/verwijderLid/{$groeplid.uid}">X</a></td>
				{/if}
			</tr>
		{/foreach}
	</table>
	{if $groep->magBewerken() AND $action!='edit'}
		<form action="/groepen/{$gtype}/{$groep->getId()}/addLid" method="post">
			{if $action=='addLid' AND $lidAdder!=false}
				{$lidAdder}
			{else}
				<input type="text" name="rawNamen" /> 
			{/if}
			<input type="submit" value="toevoegen" />
		</form>
	{/if}
</div>

<h2>{$groep->getNaam()}</h2>
{if $groep->magBewerken() AND $action=='edit'}
	{* groepformulier naar een apart bestand, is wat overzichtelijker. *}
	{include file='groepen/groepformulier.tpl'}
	
{else}
	{$groep->getSbeschrijving()|ubb}
	<div class="clear" id="voorgangerOpvolger">
		<ul class="nobullets">
		{if is_array($opvolgerVoorganger)}
			{if isset($opvolgerVoorganger.opvolger)}
				<li class="vorigeGroep"><a href="/groepen/{$gtype}/{$opvolgerVoorganger.opvolger->getId()}/">{$opvolgerVoorganger.opvolger->getNaam()}</a></li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger) OR isset($opvolgerVoorganger.opvolger)}
				<li>{$groep->getNaam()}</li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger)}
				<li class="volgendeGroep"><a href="/groepen/{$gtype}/{$opvolgerVoorganger.voorganger->getId()}/">{$opvolgerVoorganger.voorganger->getNaam()}</a></li>
			{/if}
		{/if}
		{if $groep->isAdmin()}
			<li style="margin-top: 20px;" ><a href="/groepen/{$gtype}/0/bewerken/{$groep->getSnaam()}/">Opvolger toevoegen</a></li>
		{/if}
		</ul>	
	</div> 
	{if $groep->magBewerken() OR $groep->isAdmin()}
		<div id="groepAdmin">
			{if $groep->magBewerken()}
				<a href="/groepen/{$gtype}/{$groep->getId()}/bewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk groep" /></a>
				<br /><br />
			{/if}
			{if $groep->isAdmin()}
			<a class="knop" onclick="return confirm('Weet u zeker dat u deze groep wilt verwijderen?')" href="/groepen/{$gtype}/{$groep->getId()}/verwijderen">
				<img src="{$csr_pics}forum/verwijderen.png" title="Verwijder groep" />
			</a>
			{/if}
		</div>
	{/if}
	{$groep->getBeschrijving()|ubb}
{/if}
<div class="clear"></div>