<div id="groepledenContainer">
	<ul id="tabs">
		{if $groep->isIngelogged()}
			<li id="lidlijst" class="active" onclick="return showTab({$groep->getId()}, 'lidlijst');">
				<img src="{$CSR_PICS}knopjes/lijst.png" title="Lidlijst tonen" />
			</li>
			<li id="pasfotos" onclick="return showTab({$groep->getId()}, 'pasfotos');">
				<img src="{$CSR_PICS}/knopjes/pasfoto.png" title="schakel naar pasfoto's" />
			</li>
		{/if}
		{* if $groep->magBewerken() AND $action!='edit' AND !($action=='addLid' AND $lidAdder!=false)}
		<li id="addLid" onclick="return showTab('{$groep->getId()}', 'addLid');" title="Leden toevoegen aan groep">
		<strong>+</strong>
		</li>
		{/if *}

		{if $groep->magStatsBekijken()}
			<li id="stats">
				<a onclick="showTab({$groep->getId()}, 'stats')">%</a>
			</li>
		{/if}
		{if $groep->isIngelogged()}
			<li id="emails">
				<a class="tab" onclick="showTab({$groep->getId()}, 'emails')">@</a>
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
				showTab('{/literal}{$groep->getId()}{literal}', window.location.hash.substring(1));
			} else {
		{/literal}
		{if $groep->toonPasfotos()}
				showTab('{$groep->getId()}', 'pasfotos');
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
