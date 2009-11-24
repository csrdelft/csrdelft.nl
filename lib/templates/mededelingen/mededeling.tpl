<form action="{$nieuws_root}bewerken/{$mededeling->getId()}" method="post" enctype="multipart/form-data">
	<div class="pubciemail-form">
		{$melding}
		<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="{$mededeling->getTitel()|escape:'html'}" style="width: 100%;" /><br />
		<strong>Tekst</strong>&nbsp;&nbsp;
		{* link om het tekst-vak groter te maken. *}
		<textarea id="tekst" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">{$mededeling->getTekst()|escape:'html'}</textarea><br />
		<div style="float: right;">
			<div style="position: absolute;">
				<a id="vergroot" class="handje knop" onclick="vergrootTextarea('tekst', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
			</div>
		</div>
		<div style="height: 300px; width: 30%; float: left;">Dit bericht…<br />
			<input id="prive" type="checkbox" name="prive" {if $mededeling->isPrive()}checked="checked"{/if} /><label for="prive">…alleen weergeven bij leden</label><br />
			{if $mededeling->isModerator() AND $mededeling->getZichtbaarheid()!='wacht_goedkeuring'}
				<input id="verborgen" type="checkbox" name="verborgen"{if $mededeling->isVerborgen()} checked="checked"{/if} /><label for="verborgen">…verbergen</label><br />
			{/if}
			<br />
			<strong>Categorie:</strong>
			<select name="categorie">
				{foreach from=$mededeling->getCategorie()->getAll() item=categorie}
					{if $categorie->magUitbreiden() OR $categorie->getId()==$mededeling->getCategorieId()}
						<option value="{$categorie->getId()}"{if $mededeling->getCategorieId()==$categorie->getId()} selected="selected"{/if}>{$categorie->getNaam()|escape:'html'}</option>
					{/if}
				{/foreach}
			</select><br />
			{if $mededeling->isModerator()}
			<strong>Markering:</strong>
			<select name="prioriteit">
				{foreach from=$prioriteiten key=prioriteitId item=prioriteit}
					<option value="{$prioriteitId}"{if $mededeling->getPrioriteit()==$prioriteitId} selected="selected"{/if}>{$prioriteit|escape:'html'}</option>
				{/foreach}
			</select>
			{/if}
		</div>
		<div style="height: 300px; width: 70%; float: right; ">
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
		<div style="float: left;">
			<input type="submit" name="submit" value="opslaan" />&nbsp;
			<a href="{$nieuws_root}{$mededeling->getId()}" class="knop">annuleren</a>
		</div>
	</div>
</form>