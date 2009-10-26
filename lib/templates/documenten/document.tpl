{*
 * Document toevoegen/bewerken.
 *}
 
<div id="controls">
	<a class="knop" href="/communicatie/documenten_new/">Terug</a>
</div>
<h1>Document {if $document->getID()==0}Toevoegen{else}Bewerken{/if}</h1>
<div class="foutje">{$melding}</div>

<form id="documentForm" method="post" enctype="multipart/form-data">
	<fieldset>
		<label for="naam" class="metadata">Documentnaam</label> <input type="text" name="naam" value="{$document->getNaam()}" /><br /><br />
		<label for="categorie" class="metadata">Categorie</label>
			<select name="categorie" >
				{foreach from=$categorieen item=categorie}
					<option value="{$categorie->getId()}"
					{if $categorie->getId()==$document->getCatID()}
						selected="selected"
					{/if}
					>{$categorie->getNaam()}</option>
				{/foreach}
			</select><br /><br />
		
		{foreach from=$uploaders item=uploader}
			<div class="uploadmethode">
				<div class="optie">
					{$uploader->viewRadiobutton()}
				</div>
				<div class="keuze" id="{$uploader->getNaam()}">
					{$uploader->view()}
				</div>
			</div>
		{/foreach}
		<label for="submit" class="metadata clear">&nbsp;</label><input type="submit" name="submit" value="Toevoegen" /> <a href="/communicatie/documenten_new/" class="knop">annuleren</a>
	</fieldset>
	
</form>
