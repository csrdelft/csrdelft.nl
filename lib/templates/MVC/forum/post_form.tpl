<form id="forumReageren" action="/forum/posten/{$deel->forum_id}/{$draad->draad_id}" method="post">
	<a class="forumpostlink" id="reageren">Reageren</a>
	{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
	{if !LoginModel::mag('P_LOGGED_IN')}
		<strong>
			Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de
			<a href="/actueel/groepen/Commissies/PubCie/">PubCie</a>.
			Het vermelden van <em>uw naam en email-adres</em> is verplicht.
		</strong> 
		<br /><br />
		<input type="text" name="email" class="forumEmail" placeholder="Email-adres" />
		<br /><br/>
		{* spam trap, must be kept empty! *}
		<input type="text" name="firstname" value="" class="verborgen" />
		{* ingelogde gebruikers vertellen dat iedereen hun bericht mag lezen inclusief Google. *}
	{elseif $deel->isOpenbaar()} 
		{* Openbaar forum: Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten. *}
	{/if}
	<div id="berichtPreview" class="preview forumBericht"></div>
	<textarea name="forumBericht" id="forumBericht" class="forumBericht{if $deel->isOpenbaar()} extern{/if}" rows="12" origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
	<div class="butn">
		<a class="knop float-right" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
		<a class="knop float-right arrows" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld">&uarr;&darr;</a>
		<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" />
		<input type="button" value="Voorbeeld" id="forumVoorbeeld" onclick="ubbPreview('forumBericht', 'berichtPreview');" />
		<input type="button" value="Concept opslaan" id="forumConcept" onclick="saveConceptForumBericht();" title="Blijft bewaard zolang u bent ingelogd" />
	</div>
</form>