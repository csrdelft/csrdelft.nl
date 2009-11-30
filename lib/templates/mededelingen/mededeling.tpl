<form action="{$nieuws_root}bewerken/{$mededeling->getId()}" method="post" enctype="multipart/form-data">
	{$melding}
	<strong>Titel</strong><br />
	<input type="text" name="titel" value="{$mededeling->getTitel()|escape:'html'}" style="width: 100%;" /><br />
	<strong>Tekst</strong>&nbsp;&nbsp;
	{* link om het tekst-vak groter te maken. *}
	<textarea id="tekst" name="tekst" cols="80" rows="10" style="width: 100%;">{$mededeling->getTekst()|escape:'html'}</textarea><br />
	<div style="float: right;">
		<div style="position: absolute;">
			<a id="vergroot" class="handje knop" onclick="vergrootTextarea('tekst', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
		</div>
	</div>
	<div id="instellingen">
		<strong>Categorie:</strong>
		<select name="categorie">
			{foreach from=$mededeling->getCategorie()->getAll() item=categorie}
				{if $categorie->magUitbreiden() OR $categorie->getId()==$mededeling->getCategorieId()}
					<option value="{$categorie->getId()}"{if $mededeling->getCategorieId()==$categorie->getId()} selected="selected"{/if}>{$categorie->getNaam()|escape:'html'}</option>
				{/if}
			{/foreach}
		</select><br />
		<strong>Doelgroep:</strong>
		<select name="doelgroep">
			{foreach from=$mededeling->getDoelgroepen() item=doelgroep}
				<option value="{$doelgroep}"{if $mededeling->getDoelgroep()==$doelgroep} selected="selected"{/if}>{$doelgroep}</option>
			{/foreach}
		</select>
		<br />
		{if $mededeling->isModerator()}
			<strong>Prioriteit:</strong>
			<select name="prioriteit">
				{foreach from=$prioriteiten key=prioriteitId item=prioriteit}
					<option value="{$prioriteitId}"{if $mededeling->getPrioriteit()==$prioriteitId} selected="selected"{/if}>{$prioriteit|escape:'html'}</option>
				{/foreach}
			</select><br />
		{/if}
		<input type="checkbox" name="vervaltijdAan"{if $mededeling->getVervaltijd()!==null} checked="checked"{/if} onchange="this.form.vervaltijd.disabled=this.form.vervaltijd.disabled==''?'disabled':''" />
		Vervalt op
		<input id="vervaltijd" type="text" name="vervaltijd" value="{if $mededeling->getVervaltijd()!==null}{$mededeling->getVervaltijd()|date_format:$datumtijdFormaat}{else}{$standaardVervaltijd}" disabled="disabled{/if}" />
		{if $mededeling->isModerator() AND $mededeling->getZichtbaarheid()!='wacht_goedkeuring'}
			<br /><input id="verborgen" type="checkbox" name="verborgen"{if $mededeling->isVerborgen()} checked="checked"{/if} /><label for="verborgen">Verbergen</label><br /><br />
		{/if}
	</div>
	<div id="plaatje">
		{if $mededeling->getPlaatje() != ''}
			<strong>Huidige afbeelding</strong><br />
		{else}
			<strong>Afbeelding</strong><br />
		{/if}
		{if $mededeling->getPlaatje() != ''}
			<img src="{$csr_pics}nieuws/{$mededeling->getPlaatje()|escape:'html'}" width="200px" height="200px" alt="Afbeelding" style="margin: 5px 0px 15px 0px;" /><br />
			<strong>Vervangende afbeelding</strong><br />
		{/if}
		<input type="file" name="plaatje" size="40" /><br />
		<span class="waarschuwing">(png, gif of jpg, 200x200 of groter in die verhouding.)</span>
	</div>
	<div id="knoppen">
		<input type="submit" name="submit" value="opslaan" />&nbsp;
		<a href="{$nieuws_root}{$mededeling->getId()}" class="knop">annuleren</a>
	</div>
</form>