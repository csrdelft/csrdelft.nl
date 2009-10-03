{*
 * Document toevoegen/bewerken.
 *}
 
<h2>Document {if $document->getID()==0}Toevoegen{else}Bewerken{/if}</h2>

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
		
		{if $document->hasFile()}
			<div class="uploadmethode">
				<div class="optie">
					<input type="radio" name="methode" id="radioMethodeKeepfile" value="keepfile" selectend="selected" onchange="updateForm();" /> 
					<label for="radioMethodeKeepfile">Huidige behouden</label>
				</div>
				<div id="methodeUploaden" class="keuze">
					{$file->getBestandsnaam()}
				</div>
			</div>
		{/if}
		
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="radioMethodeUploaden" value="uploaden" onchange="updateForm();" /> 
				<label for="radioMethodeUploaden">Uploaden</label>
			</div>
			<div id="methodeUploaden" class="keuze">
				<label for="fromUrl">Selecteer bestand: </label><input type="file" name="file_upload" />
			</div>
		</div>
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="radioMethodeFromurl" value="fromurl" onchange="updateForm();" /> 
				<label for="radioMethodeFromurl">Van url</label>
			</div>
			<div id="methodeUploaden" class="keuze">
				<label for="fromUrl">Geef url in: </label><input type="text" name="file_upload" value="http://" />
			</div>
		</div>
		<label for="submit" class="metadata">&nbsp;</label><input type="submit" name="submit" value="Toevoegen" />
	</fieldset>
	
</form>
