{*
 * Document toevoegen/bewerken.
 *}
 
<h2>Document {if $document->getID()==0}Toevoegen{else}Bewerken{/if}</h2>

<form id="documentForm" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend>Meta-informatie</legend>
		<label for="naam"></label> <input type="text" name="naam" value="{$document->getNaam()}" />
	</fieldset>
	{literal}
		<script>
			function showMethode(){
				buttons=document.getElementByClass
			}
		</script>
	{/literal}
	<fieldset>
		<legend>Bestand Uploaden.</legend>
		{if $document->hasFile()}
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="radioMethodeKeepfile" value="keepfile" selectend="selected" onchange="showMethode();" /> 
				<label for="radioMethodeKeepfile">Huidige behouden</label>
			</div>
			<div id="methodeUploaden" class="keuze">
				{$file->getBestandsnaam()}
			</div>
		</div>
		{/if}
		
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="radioMethodeUploaden" value="uploaden" onchange="showMethode();" /> 
				<label for="radioMethodeUploaden">Uploaden</label>
			</div>
			<div id="methodeUploaden" class="keuze">
				<label for="fromUrl">Selecteer bestand: </label><input type="file" name="file_upload" />
			</div>
		</div>
		<div class="uploadmethode">
			<div class="optie">
				<input type="radio" name="methode" id="radioMethodeFromurl" value="fromurl"onchange="showMethode();" /> 
				<label for="radioMethodeFromurl">Van url</label>
			</div>
			<div id="methodeUploaden" class="keuze">
				<label for="fromUrl">Geef url in: </label><input type="text" name="file_upload" value="http://" />
			</div>
		</div>
	</fieldset>
	
</form>