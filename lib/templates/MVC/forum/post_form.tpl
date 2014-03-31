<tr>
	<td class="auteur">
		<a class="forumpostlink" id="reageren">Reageren</a>
	</td>
	<td class="forumtekst">
		{if $deel->magPosten()}
			<form id="forumReageren" action="/forumposten/{$deel->forum_id}/{$draad->draad_id}" method="post">
				<fieldset>
					{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
					{if $draad->wacht_goedkeuring}
						<strong>
							Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de
							<a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>.
							Het vermelden van <em>uw naam en email-adres</em> is verplicht.
						</strong> 
						<br /><br />
						<label for="email">Email-adres:</label><input type="text" name="email" /><br />
						{* spam trap, must be kept empty! *}
						<input type="text" name="firstname" value="" class="verborgen" />
						{* ingelogde gebruikers vertellen dat iedereen hun bericht mag lezen inclusief Google. *}
					{elseif $deel->isOpenbaar()} 
						{* Openbaar forum: Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten. *}
					{/if}
					<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
					<textarea name="bericht" id="forumBericht" class="forumBericht {if $deel->isOpenbaar()}extern{/if}" rows="12">{$textarea}</textarea>
					<div class="butn">
						<a style="float: right; margin-right:0" class="handje knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
						<a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>

						<input type="submit" name="submit" value="opslaan" id="forumOpslaan" />
						<input type="button" value="voorbeeld" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
					</div>
				</fieldset>
			</form>
		{/if}
		{$smarty.capture.magreageren}
	</td>
</tr>