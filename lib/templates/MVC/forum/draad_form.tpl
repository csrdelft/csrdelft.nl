<tr>
	<td colspan="4" class="forumtekst">
		<form id="forumForm" action="/forumposten/{$deel->forum_id}" method="post">
			{if $loginlid->hasPermission('P_LOGGED_IN')}
				{if $deel->isOpenbaar()}
					<strong>Openbaar forum:</strong> Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten.<br /><br />
				{/if}
				Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het
				onderwerp waarover u post hier wel thuishoort.<br /><br />
			{else}
				{*	melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat
				wil zeggen, de topics zijn nog niet direct zichtbaar. *}
				Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
				&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare 
				forumdeel op in hun zoekresultaten.<br />
				<span style="text-decoration: underline;">Het is hierbij verplicht om uw naam in het bericht te plaatsen.</span><br /><br />
				<label for="email">Email-adres:</label><input type="text" id="email" name="email" /><br /><br />
				{* spam trap, must be kept empty! *}
				<input type="text" name="firstname" value="" class="verborgen" />
			{/if}
			<label for="titel"><a class="forumpostlink" style="color: #4D4D4D; text-decoration: none;" name="laatste">Titel</a></label>
			<input type="text" name="titel" id="titel" value="" class="tekst" style="width: 578px;" tabindex="1" /><br /><br />
			<label for="forumBericht">Bericht</label><div id="textareaContainer">
				<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
				<textarea name="bericht" id="forumBericht" rows="10" cols="80" class="forumBericht" tabindex="2"></textarea>
			</div>
			<div class="butn">
				<label>&nbsp;</label>
				<a style="float: right; margin-right:0" class="handje knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
				<a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
				<input type="submit" name="submit" value="opslaan" />
				<input type="button" value="voorbeeld" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
			</div>
		</form>
	</td>
</tr>