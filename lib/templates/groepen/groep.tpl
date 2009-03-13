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
<div id="groepledenContainer">
	<div class="tabjesregel">
		{if $lid->hasPermission('P_LEDEN_READ')}
		<div class="tab" onclick="return togglePasfotos('{$groep->getLedenCSV()}', document.getElementById('ledenvangroep{$groep->getId()}'));">
			<img src="{$csr_pics}/knopjes/pasfoto.png" title="schakel naar pasfoto's" />
		</div>
		{/if}
		{if $groep->magBewerken() AND $action!='edit' AND !($action=='addLid' AND $lidAdder!=false)}
		<div class="tab" title="Leden toevoegen aan groep" onclick="toggleDiv('lidAdder')">
			<strong>+</strong>
		</div>
		{/if}
		{if $groep->isAdmin() AND $groep->getStatus()=='ht'}
		<a class="tab" href="/actueel/groepen/{$gtype}/{$groep->getId()}/maakGroepOt" onclick="return confirm('Weet u zeker dat u deze groep o.t. wilt maken?')" title="Groep o.t. maken? Eindatum wordt indien niet ingevuld naar vandaag gezet.">
			<strong>&raquo;</strong>
		</a>	
		{/if}
		{if $groep->magBewerken() OR $groep->isAdmin()}
		{if $groep->magBewerken()}
		<a class="tab" href="/actueel/groepen/{$gtype}/{$groep->getId()}/bewerken#groepFormulier">
			<img src="{$csr_pics}forum/bewerken.png" title="Bewerk groep" />
		</a>
		{/if}
		{if $groep->isAdmin()}
		<a class="tab" onclick="return confirm('Weet u zeker dat u deze groep wilt verwijderen?')" href="/actueel/groepen/{$gtype}/{$groep->getId()}/verwijderen">
			<img src="{$csr_pics}forum/verwijderen.png" title="Verwijder deze groep" />
		</a>
		{/if}
		{if $groep->isAdmin()}
		<a class="tab" onclick="showStats({$groep->getId()})">%</a>
		{/if}
	{/if}
	</div>
	<div id="ledenvangroep{$groep->getId()}" class="groepleden">
		<table>
			{foreach from=$groep->getLeden() item=groeplid}
				<tr>
					<td>{$groeplid.uid|csrnaam:'civitas'}</td>
					{if $groep->toonFuncties()}<td><em>{$groeplid.functie|escape:'html'}</em></td>{/if}
					{if $groep->magBewerken()}
						<td>
						{if $groep->getTypeId()==2 AND $groep->getStatus()=='ht'}
							<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/maakLidOt/{$groeplid.uid}" title="Verplaats lid naar o.t.-groep" 
								{if !$groep->isAdmin()}onclick="return confirm('Weet u zeker dat u deze bewoner naar de oudbewonersgroep wilt verplaatsen?')"{/if}>
								&raquo;
							</a>
						{else}
							<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/verwijderLid/{$groeplid.uid}" title="Verwijder lid uit groep">X</a>
						{/if}
						</td>					
					{/if}
					
				</tr>
			{/foreach}
		</table>
	</div>
	<br />
	{if $groep->isAanmeldbaar() AND $groep->magBewerken()}
	<div class="clear"><br /></div>
		<a href="#functieOverzicht" onclick="toggleDiv('functieOverzicht')" class="knop">Toon functieoverzicht</a>
		<table id="functieOverzicht" class="verborgen clear">
			{foreach from=$groep->getFunctieAantal() key=functie item=aantal}
				{if $functie!=''}<tr><td>{$functie}</td><td>{$aantal}</td></tr>{/if}
			{/foreach}
			<tr><td><strong>Totaal</strong></td><td>{$groep->getLidCount()}</td></tr>
		</table>
	{/if}

	{if $groep->toonPasfotos() AND $lid->toonPasFotos()}
		<script type="text/javascript">togglePasfotos('{$groep->getLedenCSV()}', document.getElementById('ledenvangroep{$groep->getId()}'));</script>
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
	<div class="clear"></div>
	{if $groep->magBewerken() AND $action!='edit'}
		{if $action=='addLid' AND $lidAdder!=false}
			<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/addLid" method="post" >
				<h2>Leden toevoegen</h2>
				Hier kunt u eventueel een zinnige functie opgeven, laat het anders leeg!<br /><br />
				{$lidAdder}<input type="submit" value="toevoegen" />
			</form>
		{else}
			<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/addLid" method="post" id="lidAdder" class="verborgen">
				<h2>Leden toevoegen</h2>
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
	{$groep->getBeschrijving()|ubb}
{/if}
<div class="clear"></div>
