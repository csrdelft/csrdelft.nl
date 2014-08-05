<tr>
	<td class="auteur">
		<a class="forumpostlink" id="reageren">Reageren</a>
	</td>
	<td class="forumtekst">
		{if !$draad->verwijderd AND !$draad->gesloten AND $deel->magPosten()}
			<form id="forumReageren" action="/forum/posten/{$deel->forum_id}/{$draad->draad_id}" method="post">
				{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
				{if !LoginLid::mag('P_LOGGED_IN')}
					<strong>
						Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de
						<a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>.
						Het vermelden van <em>uw naam en email-adres</em> is verplicht.
					</strong> 
					<br /><br />
					<label for="email">Email-adres:</label><input type="text" name="email" style="width: 250px;" />
					<br /><br/>
					{* spam trap, must be kept empty! *}
					<input type="text" name="firstname" value="" class="verborgen" />
					{* ingelogde gebruikers vertellen dat iedereen hun bericht mag lezen inclusief Google. *}
				{elseif $deel->isOpenbaar()} 
					{* Openbaar forum: Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten. *}
				{/if}
				<div id="berichtPreview" class="preview"></div>
				<textarea name="bericht" id="forumBericht" class="forumBericht{if $deel->isOpenbaar()} extern{/if}" rows="12">{$post_form_tekst}</textarea>
				<div class="butn">
					<a style="float: right;" class="knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
					<a style="float: right; margin-right: 3px;" class="knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><div class="arrows">&uarr;&darr;</div>&nbsp;&nbsp;&nbsp;</a>
					<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" />
					<input type="button" value="Voorbeeld" id="forumVoorbeeld" onclick="ubbPreview('forumBericht', 'berichtPreview')"/>
				</div>
			</form>
		{/if}
		<i>{$smarty.capture.magreageren}</i>
	</td>
</tr>