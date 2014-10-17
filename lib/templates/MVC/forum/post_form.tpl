<a class="forumpostlink" id="reageren">Reageren</a>
<form id="forumForm" action="/forum/posten/{$deel->forum_id}/{$draad->draad_id}" method="post">
	{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
	{if !LoginModel::mag('P_LOGGED_IN')}
		<span class="dikgedrukt">
			Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de
			<a href="/actueel/groepen/Commissies/PubCie/">PubCie</a>.
			Het vermelden van <span class="cursief">uw naam en email-adres</span> is verplicht.
		</span> 
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
	<textarea name="forumBericht" id="forumBericht" class="forumBericht" rows="12" origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
	<div class="butn">
		<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" />
		<input type="button" value="Voorbeeld" id="forumVoorbeeld" onclick="CsrBBPreview('forumBericht', 'berichtPreview');" />
		<input type="button" value="Concept opslaan" id="forumConcept" onclick="saveConceptForumBericht();" title="Blijft bewaard zolang u bent ingelogd" />
		<div class="float-right">
			<a class="knop vergroot" data-vergroot="#forumBericht" title="Vergroot het invoerveld">&uarr;&darr;</a>
			<a class="knop opmaakhulp" title="Opmaakhulp weergeven">Opmaak</a>
		</div>
	</div>
</form>
<div id="forummeldingen">
	{if $deel->isOpenbaar()}
		<div id="public-melding">
			<div class="dikgedrukt">Openbaar forum</div>
			Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br />
			Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.
		</div>
	{/if}
</div>