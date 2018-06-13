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
					{CsrDelft\model\ProfielModel::getNaam(CsrDelft\model\security\LoginModel::getUid(), 'user')}
					{if CsrDelft\model\LidInstellingenModel::get('forum', 'toonpasfotos') == 'nee'}
						<span id="t{CsrDelft\model\security\LoginModel::getUid()}-reageren" class="togglePasfoto" title="Toon pasfoto">&raquo;</span>
					{/if}
				</td>
				<td class="postlinktd"><a id="reageren" name="reageren" class="postlink">&rarr;</a></td>
			</tr>
		</table>

		{toegang P_LEDEN_READ}
			<div id="preageren" class="forumpasfoto{if CsrDelft\model\LidInstellingenModel::get('forum', 'toonpasfotos') == 'nee'} verborgen">{else}{toegang P_LEDEN_READ}">{CsrDelft\model\ProfielModel::getLink(CsrDelft\model\security\LoginModel::getUid(), 'pasfoto')}{/toegang}{/if}</div>
		{/toegang}

		<div id="forummeldingen">
			{if $deel->isOpenbaar()}
				<div id="public-melding">
					<div class="dikgedrukt">Openbaar forum</div>
					Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br />
					Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.
				</div>
			{/if}
			{toegang P_LOGGED_IN}
				<div id="draad-melding">
					Hier kunt u een onderwerp toevoegen in deze categorie van het forum.
					Kijkt u vooraf goed of het onderwerp waarover u post hier wel thuishoort.
				</div>
			{/toegang}
		</div>

	</td>
	<td class="bericht0">

		<form id="forumForm" class="Formulier" action="/forum/posten/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}" method="post">
			<input type="hidden" name="_token" value="{CSRF_TOKEN}" />
			{toegang P_LOGGED_IN}
			{geentoegang}
				<div class="bericht">
					Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare
					forumdeel op in hun zoekresultaten.<br />
					Het vermelden van <span class="cursief">uw naam en e-mailadres</span> is verplicht.
				</div>
				<input type="text" name="email" class="FormElement TextField forumEmail" placeholder="E-mailadres" />
				<input type="text" name="firstname" value="" class="FormElement TextField verborgen" />{* spam trap, must be kept empty! *}
				<br /><br />
			{/toegang}
			{if $draad === null}
				<input type="text" id="nieuweTitel" name="titel" class="FormElement TextField" tabindex="1" placeholder="Onderwerp titel" value="{$post_form_titel}" origvalue="{$post_form_titel}" />
				<br /><br />
			{/if}
			<div id="berichtPreview" class="bbcodePreview forumBericht"></div>
			<textarea name="forumBericht" id="forumBericht" class="FormElement BBCodeField forumBericht" tabindex="2" rows="12" origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
			<div class="butn">
				<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" class="btn" />
				<input type="button" value="Voorbeeld" id="forumVoorbeeld" class="btn" onclick="window.bbcode.CsrBBPreview('forumBericht', 'berichtPreview');" />
				{toegang P_LOGGED_IN}
					<input type="button" value="Concept opslaan" id="forumConcept" class="btn" onclick="window.forum.saveConceptForumBericht();" data-url="/forum/concept/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}" />
				{/toegang}
				<div class="float-right">
					{toegang P_LOGGED_IN}
						<a href="/fotoalbum/uploaden/fotoalbum/{CsrDelft\model\groepen\LichtingenModel::getHuidigeJaargang()}/Posters" target="_blank">Poster opladen</a> &nbsp;
						<a href="/groepen/activiteiten/nieuw" class="post popup">Ketzer maken</a> &nbsp;
						<a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a> &nbsp;
					{/toegang}
				</div>
			</div>

		</form>
		<br /><br />

	</td>
</tr>
