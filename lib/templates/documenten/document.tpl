{*
 * Document toevoegen/bewerken.
 *}
 
<h2>Document {if $document->getID()==0}Toevoegen{else}Bewerken{/if}</h2>
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
		
		{if $document->hasFile()}
			<div class="uploadmethode">
				<div class="optie">
					<input type="radio" name="methode" id="rMethodeKeepfile" value="keepfile" selectend="selected" /> 
					<label for="rMethodeKeepfile">Huidige behouden</label>
				</div>
				<div id="MethodeKeepfile" class="keuze">
					{$file->getBestandsnaam()}
				</div>
			</div>
		{/if}
		
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="rMethodeUploaden" value="uploaden" /> 
				<label for="rMethodeUploaden">Uploaden</label>
			</div>
			<div id="MethodeUploaden" class="keuze">
				<label for="fromUrl">Selecteer bestand: </label><input type="file" name="file_upload" />
			</div>
		</div>
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="rMethodeFromurl" value="fromurl"  /> 
				<label for="rMethodeFromurl">Van url</label>
			</div>
			<div id="MethodeFromurl" class="keuze">
				<label for="fromUrl">Geef url op:</label><input type="text" name="url" class="fromurl" value="http://" />
			</div>
		</div>
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="rMethodePublicftp" value="publicftp"  /> 
				<label for="rMethodePublicftp">Van publieke ftp</label>
			</div>
			<div id="MethodePublicftp" class="keuze">
				<label for="publicftp">Selecteer een bestand:  </label><div id="ftpOpties" style="margin-left: 300px;"><li>Bestand1.pdf</li><li>Bestand2.odf</li><li>Lezing.mp3</li></div>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		<label for="submit" class="metadata">&nbsp;</label><input type="submit" name="submit" value="Toevoegen" /> <a href="/communicatie/documenten_new/" class="knop">annuleren</a>
	</fieldset>
	
</form>
