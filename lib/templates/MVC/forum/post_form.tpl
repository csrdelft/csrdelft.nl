<tr>
	<td class="auteur">

		<table>
			<tr>
				{* todo: invoerveld voor naam niet-ingelogd*}
				<td>{LoginModel::getUid()|csrnaam:'user':'visitekaartje'}</td>
				<td class="postlinktd"><a class="postlink">&rarr;</a></td>
			</tr>
		</table>

		{if LoginModel::mag('P_LEDEN_READ')}
			{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'}
				<span id="t{LoginModel::getUid()}-reageren" class="togglePasfoto" title="Toon pasfoto">&raquo;</span>
			{/if}
			<div id="preageren" class="forumpasfoto{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'} verborgen">{elseif LoginModel::mag('P_LEDEN_READ')}">{LoginModel::getUid()|csrnaam:'pasfoto'}{/if}</div>
		{/if}

		<div id="forummeldingen">
			{if $deel->isOpenbaar()}
				<div id="public-melding">
					<div class="dikgedrukt">Openbaar forum</div>
					Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br />
					Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.
				</div>
			{/if}
			{if LoginModel::mag('P_LOGGED_IN')}
				<div id="draad-melding">
					Hier kunt u een onderwerp toevoegen in deze categorie van het forum.
					Kijkt u vooraf goed of het onderwerp waarover u post hier wel thuishoort.
				</div>
			{/if}
		</div>

	</td>
	<td>
		<form id="forumForm" action="/forum/posten/{$deel->forum_id}{if isset($daad)}/{$draad->draad_id}{/if}" method="post">

			{if !LoginModel::mag('P_LOGGED_IN')}
				<div class="bericht">
					Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare
					forumdeel op in hun zoekresultaten.<br />
					Het vermelden van <span class="cursief">uw naam en email-adres</span> is verplicht.
				</div>
				<label for="email" class="externeemail">Email-adres</label>
				<input type="text" id="email" name="email" class="forumEmail" placeholder="Email-adres" />
				<input type="text" name="firstname" value="" class="verborgen" />{* spam trap, must be kept empty! *}
			{/if}
			{if $draad === null}
				<input type="text" name="titel" id="titel" value="" class="tekst" placeholder="Onderwerp titel" />
				<br /><br />
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

		<br /><br />
	</td>
</tr>