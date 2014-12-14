<tr>
	<td colspan="2">
		{include file='forum/draad_reageren.tpl'}
	</td>
</tr>
<tr id="forumPosten">
	<td class="auteur">

		<table>
			<tr>
				{* todo: invoerveld voor naam niet-ingelogd*}
				<td>
					{LoginModel::getUid()|csrnaam:'user'}
					{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'}
						<span id="t{LoginModel::getUid()}-reageren" class="togglePasfoto" title="Toon pasfoto">&raquo;</span>
					{/if}
				</td>
				<td class="postlinktd"><a id="reageren" name="reageren" class="postlink">&rarr;</a></td>
			</tr>
		</table>

		{if LoginModel::mag('P_LEDEN_READ')}
			<div id="preageren" class="forumpasfoto{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'} verborgen">{elseif LoginModel::mag('P_LEDEN_READ')}">{LoginModel::getUid()|csrnaam:'pasfoto':'link'}{/if}</div>
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
	<td class="bericht0">

		<form id="forumForm" class="Formulier" action="/forum/posten/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}" method="post">

			{if !LoginModel::mag('P_LOGGED_IN')}
				<div class="bericht">
					Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare
					forumdeel op in hun zoekresultaten.<br />
					Het vermelden van <span class="cursief">uw naam en email-adres</span> is verplicht.
				</div>
				<input type="text" name="email" class="FormElement TextField forumEmail" placeholder="Email-adres" />
				<input type="text" name="firstname" value="" class="FormElement TextField verborgen" />{* spam trap, must be kept empty! *}
				<br /><br />
			{/if}
			{if $draad === null}
				<input type="text" id="nieuweTitel" name="titel" value="{$post_form_titel}" origvalue="{$post_form_titel}" class="FormElement TextField" placeholder="Onderwerp titel" />
				<br /><br />
			{/if}
			<div id="berichtPreview" class="preview forumBericht"></div>
			<textarea name="forumBericht" id="forumBericht" class="FormElement CsrBBPreviewField forumBericht" rows="12" origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
			<div class="butn">
				<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" class="btn" />
				<input type="button" value="Voorbeeld" id="forumVoorbeeld" class="btn" onclick="CsrBBPreview('forumBericht', 'berichtPreview');" />
				{if LoginModel::mag('P_LOGGED_IN')}
					<input type="button" value="Concept opslaan" id="forumConcept" class="btn" onclick="saveConceptForumBericht();" data-url="/forum/concept/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}" />
				{/if}
				<div class="float-right">
					{if LoginModel::mag('P_LOGGED_IN')}
						<a href="/fotoalbum/uploaden/fotoalbum/{Lichting::getHuidigeJaargang()}/Posters" target="_blank">Poster opladen</a> &nbsp;
						<a href="/groepen/Ketzers" target="_blank">Ketzer maken</a> &nbsp;
						<a href="http://csrdelft.nl/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a> &nbsp;
					{/if}
				</div>
			</div>

		</form>
		<br /><br />

	</td>
</tr>