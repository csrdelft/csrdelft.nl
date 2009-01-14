{$melding}
<ul class="horizontal nobullets">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groep->getTypeId()}<strong>{/if}
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groep->getTypeId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
<div id="groepleden">
	{if $groep->toonPasfotos()}
		<div class="pasfotomatrix">
			{foreach from=$groep->getLeden() item=groeplid}
				{$groeplid.uid|pasfoto}
			{/foreach}
		</div>
	{else}
		<table>
			{foreach from=$groep->getLeden() item=groeplid}
				<tr>
					<td>{$groeplid.uid|csrnaam:'civitas'}</td>
					{if $groep->toonFuncties()}<td><em>{$groeplid.functie|escape:'html'}</em></td>{/if}
					{if $groep->magBewerken()}
						<td><a href="/actueel/groepen/{$gtype}/{$groep->getId()}/verwijderLid/{$groeplid.uid}">X</a></td>
					{/if}
				</tr>
			{/foreach}
		</table>
		
		{if $groep->isAanmeldbaar() AND $groep->magBewerken()}
			<a href="#functieOverzicht" onclick="toggleDiv('functieOverzicht')" class="knop">Toon functieoverzicht</a>
			<table id="functieOverzicht" class="verborgen">
				{foreach from=$groep->getFunctieAantal() key=functie item=aantal}
					{if $functie!=''}<tr><td>{$functie}</td><td>{$aantal}</td></tr>{/if}
				{/foreach}
				<tr><td><strong>Totaal</strong></td><td>{$groep->getLidCount()}</td></tr>
			</table>
		{/if}
	{/if}
	{if $groep->magAanmelden()}
		<a href="#aanmeldForm" onclick="toggleDiv('aanmeldForm')" class="knop">aanmelden</a>
		<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/aanmelden" method="post" id="aanmeldForm" class="verborgen">
			U kunt zich hier aanmelden voor deze groep.
			{if $groep->getToonFuncties()!='niet'}
				Geef ook een opmerking/functie op:<br /> 
				<input type="text" name="functie" />
			{else}
				<br />
			{/if}
			<input type="submit" value="aanmelden" />
		</form>
	{elseif $groep->isAanmeldbaar() AND $groep->isVol()}
		Deze groep is vol, u kunt zich niet meer aanmelden.
	{/if}
	<div  class="clear"></div>
	{if $groep->magBewerken() AND $action!='edit'}
		{if $action=='addLid' AND $lidAdder!=false}
			<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/addLid" method="post" >
				Hier kunt u eventueel een zinnige functie opgeven, laat het anders leeg!<br /><br />
				{$lidAdder}<input type="submit" value="toevoegen" />
			</form>
		{else}
			<a onclick="toggleDiv('lidAdder')" class="knop" href="#">Leden toevoegen</a><br />
			<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/addLid" method="post" id="lidAdder" class="verborgen">
				Voer hier door komma's gescheiden namen of uid's in:<br /><br />
				Zoek ook in: <input type="checkbox" name="filterOud" id="filterOud" /> <label for="filterOud">oudleden</label>
				
				{if $groep->isAdmin()}
					<input type="checkbox" name="filterNobody" id="filterNobody" /> <label for="filterNobody">nobodies</label>
				{/if}<br /><br />
				<input type="text" name="rawNamen" />
				<input type="submit" value="toevoegen" />
			</form>
		{/if}
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
				<li class="vorigeGroep"><a href="/actueel/groepen/{$gtype}/{$opvolgerVoorganger.opvolger->getId()}/">{$opvolgerVoorganger.opvolger->getNaam()}</a></li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger) OR isset($opvolgerVoorganger.opvolger)}
				<li>{$groep->getNaam()}</li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger)}
				<li class="volgendeGroep"><a href="/actueel/groepen/{$gtype}/{$opvolgerVoorganger.voorganger->getId()}/">{$opvolgerVoorganger.voorganger->getNaam()}</a></li>
			{/if}
		{/if}
		{if $groep->isAdmin()}
			<li style="margin-top: 20px;" ><a href="/actueel/groepen/{$gtype}/0/bewerken/{$groep->getSnaam()}/">Opvolger toevoegen</a></li>
		{/if}
		</ul>	
	</div> 
	{if $groep->magBewerken() OR $groep->isAdmin()}
		<div id="groepAdmin">
			{if $groep->magBewerken()}
				<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/bewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" 
title="Bewerk groep" /></a>
				<br /><br />
			{/if}
			{if $groep->isAdmin()}
			<a class="knop" onclick="return confirm('Weet u zeker dat u deze groep wilt verwijderen?')" href="/actueel/groepen/{$gtype}/{$groep->getId()}/verwijderen">
				<img src="{$csr_pics}forum/verwijderen.png" title="Verwijder groep" />
			</a>
			{/if}
		</div>
	{/if}
	{$groep->getBeschrijving()|ubb}
{/if}
<div class="clear"></div>
