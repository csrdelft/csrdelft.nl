<h1>Mededeling {if $mededeling->id==0}toevoegen{else}bewerken{/if}</h1>
<form action="/mededelingen/bewerken/{$mededeling->id}" method="post" enctype="multipart/form-data">
	{printCsrfField()}
	{getMelding()}
	{toegang P_NEWS_MOD}
	{geentoegang}
		Hier kunt u een mededeling toevoegen. Het zal echter niet direct zichtbaar worden, maar &eacute;&eacute;rst door de PubCie worden goedgekeurd.<br /><br />
	{/toegang}
	<label>Titel:</label>
	<input type="text" name="titel" value="{$mededeling->titel|escape:'html'}" class="titel" /><br />
	<label>Tekst:</label>
	<div class="indent">
		<div id="preview_tekst" class="bbcodePreview"></div>
		<textarea id="tekst" name="tekst" class="BBCodeField breed" rows="12" style="resize:vertical;">{$mededeling->tekst|escape:'html'}</textarea><br />
		<a id="voorbeeld" class="btn" data-bbpreview-btn="tekst">Voorbeeld</a>
		<a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a>
	</div>

	<div id="instellingen">
		<label for="categorie">Categorie: <a title="De categorie bepaalt welk kleurtje erv&oacute;&oacute;r komt in de overzichtspagina.">{icon get="vraagteken"}</a></label>
		<select name="categorie">
			{foreach from=$categorien item=categorie}
				{if $categorie->magUitbreiden() OR $categorie->id==$mededeling->categorie}
					<option value="{$categorie->id}"{if $mededeling->categorie==$categorie->id} selected="selected"{/if}>{$categorie->naam|escape:'html'}</option>
				{/if}
			{/foreach}
		</select><br />
		<label for="doelgroep">Doelgroep: <a title="De doelgroep bepaalt welke groep(en) mensen het recht krijg(t)(en) om deze mededeling te zien.">{icon get="vraagteken"}</a></label>
		<select name="doelgroep">
			{foreach from=$doelgroepen item=doelgroep}
				<option value="{$doelgroep}"{if $mededeling->doelgroep==$doelgroep} selected="selected"{/if}>{$doelgroep}{if $doelgroep === 'iedereen'} (ook externen){/if}</option>
			{/foreach}
		</select><br />
		{if $mededeling->isModerator()}
			<label for="prioriteit">Prioriteit: <a title="Hoe belangrijk is deze mededeling? De mededelingen met de hoogste prioriteit komt bovenaan in de top 3 op de voorpagina van de stek.">{icon get="vraagteken"}</a></label>
			<select name="prioriteit">
				{foreach from=$prioriteiten key=prioriteitId item=prioriteit}
					<option value="{$prioriteitId}"{if $mededeling->prioriteit==$prioriteitId} selected="selected"{/if}>{$prioriteit|escape:'html'}</option>
				{/foreach}
			</select><br />
		{/if}
		<label>Vervalt op:</label>
		<div id="vervalt">
			<input type="checkbox" name="vervaltijdAan"{if $mededeling->vervaltijd!==null} checked="checked"{/if} onchange="this.form.vervaltijd.disabled = this.form.vervaltijd.disabled === '' ? 'disabled' : ''" />&nbsp;
			<input id="vervaltijd" type="text" name="vervaltijd" value="{if $mededeling->vervaltijd!==null}{$mededeling->vervaltijd|date_format:$datumtijdFormaat}{else}{$standaardVervaltijd}" disabled="disabled{/if}" />
		</div><br />
		{if $mededeling->isModerator() AND $mededeling->zichtbaarheid!='wacht_goedkeuring'}
			<label for="verborgen">Verbergen <a title="Verborgen mededelingen zijn alleen voor moderators zichtbaar.">{icon get="vraagteken"}</a></label>
			<input id="verborgen" type="checkbox" name="verborgen"{if $mededeling->verborgen} checked="checked"{/if} />
		{/if}
	</div>
	<div id="plaatje">
		{if $mededeling->plaatje != ''}
			<strong>Huidige afbeelding</strong><br />
		{else}
			<strong>Afbeelding</strong><br />
		{/if}
		{if $mededeling->plaatje != ''}
			<img src="/plaetjes/mededeling/{$mededeling->plaatje|escape:'html'}" width="200px" height="200px" alt="Afbeelding" style="margin: 5px 0 15px 0;" /><br />
			<strong>Vervangende afbeelding</strong><br />
		{/if}
		<input type="file" name="plaatje" /><br />
		<span>(png, gif of jpg, 200x200 of groter in die verhouding)</span>
	</div>
	<div class="clear">
		{if $prullenbak}<input type="hidden" name="prullenbak" value="1" />{/if}
		<label >&nbsp;</label><input type="submit" name="submit" value="Opslaan" />
		<a href="/mededelingen/{$mededeling->id}" class="btn">Annuleren</a>
	</div>
</form>
