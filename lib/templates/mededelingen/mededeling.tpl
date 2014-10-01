<h1>Mededeling {if $mededeling->getId()==0}toevoegen{else}bewerken{/if}</h1>
<form action="{MededelingenContent::mededelingenRoot}bewerken/{$mededeling->getId()}" method="post" enctype="multipart/form-data">
	{SimpleHtml::getMelding()}
	{if !$mededeling->isModerator()}
		Hier kunt u een mededeling toevoegen. Het zal echter niet direct zichtbaar worden, maar &eacute;&eacute;rst door de PubCie worden goedgekeurd.<br /><br />
	{/if} 
	<label>Titel:</label>
	<input type="text" name="titel" value="{$mededeling->getTitel()|escape:'html'}" class="titel" /><br />
	<label>Tekst:</label>
	<div class="indent">
		<div id="bewerkPreview" class="preview"></div>
		<textarea id="tekst" name="tekst" rows="12" class="tekst">{$mededeling->getTekst()|escape:'html'}</textarea><br />
		<a id="voorbeeld" class="knop" onclick="return ubbPreview('tekst', 'bewerkPreview')">Voorbeeld</a>
		<a id="vergroot" class="knop" onclick="vergrootTextarea('tekst', 10)" title="Vergroot het invoerveld"><div class="arrows">&uarr;&darr;</div>&nbsp;&nbsp;&nbsp;&nbsp;</a>
		<a id="opmaakhulp" class="knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
	</div>

	<div id="instellingen">
		<label for="categorie">Categorie: <a title="De categorie bepaalt welk kleurtje erv&oacute;&oacute;r komt in de overzichtspagina.">{icon get="vraagteken"}</a></label>
		<select name="categorie">
			{foreach from=$mededeling->getCategorie()->getAll() item=categorie}
				{if $categorie->magUitbreiden() OR $categorie->getId()==$mededeling->getCategorieId()}
					<option value="{$categorie->getId()}"{if $mededeling->getCategorieId()==$categorie->getId()} selected="selected"{/if}>{$categorie->getNaam()|escape:'html'}</option>
				{/if}
			{/foreach}
		</select><br />
		<label for="doelgroep">Doelgroep: <a title="De doelgroep bepaalt welke groep(en) mensen het recht krijg(t)(en) om deze mededeling te zien.">{icon get="vraagteken"}</a></label>
		<select name="doelgroep">
			{foreach from=$mededeling->getDoelgroepen() item=doelgroep}
				<option value="{$doelgroep}"{if $mededeling->getDoelgroep()==$doelgroep} selected="selected"{/if}>{$doelgroep}{if $doelgroep === 'iedereen'} (ook externen){/if}</option>
			{/foreach}
		</select><br />
		{if $mededeling->isModerator()}
			<label for="prioriteit">Prioriteit: <a title="Hoe belangrijk is deze mededeling? De mededelingen met de hoogste prioriteit komt bovenaan in de top {MededelingenContent::aantalTopMostBlock} op de voorpagina van de stek.">{icon get="vraagteken"}</a></label>
			<select name="prioriteit">
				{foreach from=$prioriteiten key=prioriteitId item=prioriteit}
					<option value="{$prioriteitId}"{if $mededeling->getPrioriteit()==$prioriteitId} selected="selected"{/if}>{$prioriteit|escape:'html'}</option>
				{/foreach}
			</select><br />
		{/if}
		<label>Vervalt op:</label>
		<div id="vervalt">
			<input type="checkbox" name="vervaltijdAan"{if $mededeling->getVervaltijd()!==null} checked="checked"{/if} onchange="this.form.vervaltijd.disabled = this.form.vervaltijd.disabled == '' ? 'disabled' : ''" />&nbsp;
			<input id="vervaltijd" type="text" name="vervaltijd" value="{if $mededeling->getVervaltijd()!==null}{$mededeling->getVervaltijd()|date_format:$datumtijdFormaat}{else}{$standaardVervaltijd}" disabled="disabled{/if}" />
		</div><br />
		{if $mededeling->isModerator() AND $mededeling->getZichtbaarheid()!='wacht_goedkeuring'}
			<label for="verborgen">Verbergen <a title="Verborgen mededelingen zijn alleen voor moderators zichtbaar.">{icon get="vraagteken"}</a></label> 
			<input id="verborgen" type="checkbox" name="verborgen"{if $mededeling->isVerborgen()} checked="checked"{/if} />
		{/if}
	</div>
	<div id="plaatje">
		{if $mededeling->getPlaatje() != ''}
			<strong>Huidige afbeelding</strong><br />
		{else}
			<strong>Afbeelding</strong><br />
		{/if}
		{if $mededeling->getPlaatje() != ''}
			<img src="{$CSR_PICS}/nieuws/{$mededeling->getPlaatje()|escape:'html'}" width="200px" height="200px" alt="Afbeelding" style="margin: 5px 0 15px 0;" /><br />
			<strong>Vervangende afbeelding</strong><br />
		{/if}
		<input type="file" name="plaatje" size="40" /><br />
		<span>(png, gif of jpg, 200x200 of groter in die verhouding)</span>
	</div>
	<div class="clear">
		{if $prullenbak}<input type="hidden" name="prullenbak" value="1" />{/if}
		<label >&nbsp;</label><input type="submit" name="submit" value="Opslaan" />
		<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}" class="knop">Annuleren</a>
	</div>
</form>