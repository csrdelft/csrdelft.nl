<form action="{$nieuws_root}bewerken/{$mededeling->getId()}" method="post" enctype="multipart/form-data">
	<div class="pubciemail-form">
		{$mededeling->getMelding()|escape:'html'}
		<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="{$mededeling->getTitel()|escape:'html'}" style="width: 100%;" /><br />
		<strong>Bericht</strong>&nbsp;&nbsp;
		{* link om het tekst-vak groter te maken. *}
		<a href="#" onclick="vergrootTextarea('nieuwsBericht', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />
		<textarea id="nieuwsBericht" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">{$mededeling->getTekst()|escape:'html'}</textarea><br />
		<div style="height: 200px; width: 30%; float: left;">Dit bericht…<br />
			<input id="prive" type="checkbox" name="prive" {if $mededeling->isPrive()}checked="checked"{/if} /><label for="prive">…alleen weergeven bij leden</label><br />
			<input id="verborgen" type="checkbox" name="verborgen" {if $mededeling->isVerborgen()}checked="checked"{/if} /><label for="verborgen">…verbergen</label><br />
			<br />
			Categorie:
			<select name="categorie">
				{foreach from=$mededeling->getCategorie()->getAll() item=categorie}
					<option value="{$categorie->getId()}">{$categorie->getNaam()|escape:'html'}</option>
				{/foreach}
			</select><br />
			Markering:
			<select name="rank">
				{foreach from=$markeringen item=markering}
					<option value="{$markering.rank}">{$markering.naam|escape:'html'}</option>
				{/foreach}
			</select>
		</div>
		<div style="height: 200px; width: 70%; float: right; ">
			{if $mededeling->getPlaatje() != ''}
				<img src="{$csr_pics}nieuws/{$mededeling->getPlaatje()|escape:'html'}" width="200px" height="200px" alt="Afbeelding" style="float: left; margin-right: 10px;" />
			{/if}
			<strong>Afbeelding bij de mededeling</strong><br />
			Afbeelding:<br /><input type="file" name="plaatje" size="40" /><br />
			<span class="waarschuwing">(png, gif of jpg, 200x200 of groter in die verhouding.)</span>
		</div>
		<input type="submit" name="submit" value="opslaan" />&nbsp;
		<a href="{$nieuws_root}{$mededeling->getId()}" class="knop">annuleren</a>
	</div>
</form>