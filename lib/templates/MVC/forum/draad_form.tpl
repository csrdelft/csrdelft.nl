<form id="forumForm" action="/forum/posten/{$deel->forum_id}" method="post">
	<a class="forumpostlink" id="nieuwonderwerp">Nieuw onderwerp</a><br />
	{if LoginModel::mag('P_LOGGED_IN')}
		Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het
		onderwerp waarover u post hier wel thuishoort.<br /><br />
	{else}
		{*	melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat
		wil zeggen, de topics zijn nog niet direct zichtbaar. *}
		Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
		&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare
		forumdeel op in hun zoekresultaten.<br />
		<span class="onderstreept">Het is hierbij verplicht om uw naam in het bericht te plaatsen.</span><br /><br />
		<label for="email" class="externeemail">Email-adres</label>
		<input type="text" id="email" name="email" /><br /><br />
		{* spam trap, must be kept empty! *}
		<input type="text" name="firstname" value="" class="verborgen" />
	{/if}
	<input type="text" name="titel" id="titel" value="" class="tekst" placeholder="Onderwerp titel" /><br /><br />
	<div id="berichtPreview" class="preview forumBericht"></div>
	<textarea name="forumBericht" id="forumBericht" class="forumBericht{if $deel->isOpenbaar()} extern{/if}" rows="12" origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
	<div class="butn">
		<a class="knop float-right" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
		<a class="knop float-right" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><div class="arrows">&uarr;&darr;</div>&nbsp;&nbsp;&nbsp;&nbsp;</a>
		<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" />
		<input type="button" value="Voorbeeld" id="forumVoorbeeld" onclick="ubbPreview('forumBericht', 'berichtPreview');" />
		<input type="button" value="Concept opslaan" id="forumConcept" onclick="saveConcept();" title="Blijft bewaard totdat u uitlogd!" />
	</div>
</form>