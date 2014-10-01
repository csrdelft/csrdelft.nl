{SimpleHtml::getMelding()}
<ul class="horizontal nobullets">
	{foreach from=$groeptypes item=groeptype}
		<li{if $groeptype.id==$groep->getTypeId()} class="active"{/if}>
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		</li>
	{/foreach}
</ul>
<hr />
{if $groep->getId()!=0}
	<div id="groepledenContainer">
		<ul id="tabs">
			{if $groep->isIngelogged()}
				<li id="lidlijst" class="active" onclick="return groepTabShow({$groep->getId()}, 'lidlijst');">
					<img src="{$CSR_PICS}/knopjes/lijst.png" title="Lidlijst tonen" />
				</li>
				<li id="pasfotos" onclick="return groepTabShow({$groep->getId()}, 'pasfotos');">
					<img src="{$CSR_PICS}/knopjes/pasfoto.png" title="schakel naar pasfoto's" />
				</li>
			{/if}
			{* if $groep->magBewerken() AND $action!='edit' AND !($action=='addLid' AND $lidAdder!=false)}
			<li id="addLid" onclick="return groepTabShow('{$groep->getId()}', 'addLid');" title="Leden toevoegen aan groep">
			<strong>+</strong>
			</li>
			{/if *}
			{if $groep->magStatsBekijken()}
				<li id="stats">
					<a onclick="groepTabShow({$groep->getId()}, 'stats')">%</a>
				</li>
			{/if}
			{if $groep->isIngelogged()}
				<li id="emails">
					<a class="tab" onclick="groepTabShow({$groep->getId()}, 'emails')">@</a>
				</li>
			{/if}
		</ul>
		<div id="ledenvangroep{$groep->getId()}" class="groepleden">
			{include file='groepen/groepleden.tpl'}
		</div>
		<br />
		{* 	we laden het juiste tabje adh van de hashtag, als er niets
		ingesteld is kiezen we tussen pasfoto's en ledenlijst aan de hand
		van de instelling van de gebruiker.
		*}
		<script type="text/javascript">
			{literal}
				if (window.location.hash != '') {
					groepTabShow('{/literal}{$groep->getId()}{literal}', window.location.hash.substring(1));
				} else {
			{/literal}
			{if $groep->toonPasfotos()}
					groepTabShow('{$groep->getId()}', 'pasfotos');
			{/if}
			{literal}
				}
			{/literal}
		</script>


		<div class="clear"></div>
		{if $groep->magBewerken() AND $action!='edit'}
			{if $action=='addLid' AND $lidAdder!=false}
				<form action="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/addLid" method="post" >
					<h2>Leden toevoegen</h2>
					Hier kunt u eventueel een zinnige functie opgeven, laat het anders leeg!<br />(bij meerdere selectiemenu's opties scheiden met &&)<br />
					<br />
					{$lidAdder}<input type="submit" value="toevoegen" />
				</form>
			{else}
				<a class="knop" onclick="$('#lidAdder').toggle();
						this.parentNode.removeChild(this)">leden toevoegen</a>
				<form action="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/addLid" method="post" id="lidAdder" class="verborgen">
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
{/if}

<h2>{$groep->getNaam()}</h2>
{if $groep->magBewerken() AND $action=='edit'}
	{* groepformulier naar een apart bestand, is wat overzichtelijker. *}
	{include file='groepen/groepformulier.tpl'}
{else}
	{if $groep->getTypeId()==11 }Ouderejaars: {$groep->getEigenaar()}<br /><br />{/if} {* alleen bij Sjaarsacties *}
	{$groep->getSbeschrijving()|ubb}
	<div class="clear" id="voorgangerOpvolger">
		<ul class="nobullets">
			{if is_array($opvolgerVoorganger)}
				{if isset($opvolgerVoorganger.opvolger)}
					<li class="vorigeGroep"><a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$opvolgerVoorganger.opvolger->getId()}/">{$opvolgerVoorganger.opvolger->getNaam()}</a></li>
					{/if}
					{if isset($opvolgerVoorganger.voorganger) OR isset($opvolgerVoorganger.opvolger)}
					<li>{$groep->getNaam()}</li>
					{/if}
					{if isset($opvolgerVoorganger.voorganger)}
					<li class="volgendeGroep"><a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$opvolgerVoorganger.voorganger->getId()}/">{$opvolgerVoorganger.voorganger->getNaam()}</a></li>
					{/if}
				{/if}
				{if $groep->isAdmin() OR $groep->isEigenaar()}
				<li style="margin-top: 20px;">
					<a href="/actueel/groepen/{$groep->getType()->getNaam()}/0/bewerken/{$groep->getId()}">Opvolger toevoegen</a>
				</li>
			{/if}
		</ul>
	</div>
	{if $groep->isAdmin() OR $groep->magBewerken()}
		<div id="groepAdmin">
			{if ($groep->isAdmin() OR $groep->isEigenaar()) AND $groep->getStatus()=='ht'}
				<a class="knop" href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/maakGroepOt" onclick="return confirm('Weet u zeker dat u deze groep o.t. wilt maken?');" title="Groep o.t. maken? Eindatum wordt indien niet ingevuld naar vandaag gezet.">
					<strong>&raquo;</strong>
				</a>
			{/if}

			{if $groep->magBewerken()}
				<a class="knop" href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/bewerken#groepFormulier">
					<img src="{$CSR_PICS}/famfamfam/pencil.png" title="Bewerk groep" />
				</a>
			{/if}
			{if $groep->isAdmin()}
				<a class="knop" onclick="return confirm('Weet u zeker dat u deze groep wilt verwijderen?');" href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/verwijderen">
					<img src="{$CSR_PICS}/forum/verwijderen.png" title="Verwijder deze groep" />
				</a>
			{/if}
		</div>
	{/if}
	{$groep->getBeschrijving()|ubb}
	{/if}
		<div class="clear"></div>
