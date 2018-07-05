<div>
	{include file='forum/draad_reageren.tpl'}
</div>

<div id="forumPosten" class="forum-posten">
	<div class="auteur">
		<div class="postpijl">
			<a class="postanchor"></a>
			<a class="postlink">&rarr;</a>
		</div>
		<div class="naam">
			{CsrDelft\model\ProfielModel::getNaam(CsrDelft\model\security\LoginModel::getUid(), 'user')}
		</div>

		{toegang P_LEDEN_READ}
			<div class="forumpasfoto">{CsrDelft\model\ProfielModel::getLink(CsrDelft\model\security\LoginModel::getUid(), 'pasfoto')}</div>
		{/toegang}
	</div>

	<div class="bericht0">

		<form id="forumForm" class="Formulier"
					action="/forum/posten/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}" method="post">
			{toegang P_LOGGED_IN}
			{geentoegang}
				<div class="bericht">
					Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare
					forumdeel op in hun zoekresultaten.<br/>
					Het vermelden van <span class="cursief">uw e-mailadres</span> is verplicht.
				</div>
				<input type="text" name="email" class="FormElement TextField forumEmail" placeholder="E-mailadres"/>
				<input type="text" name="firstname" value="" class="FormElement TextField verborgen"/>
				{* spam trap, must be kept empty! *}
			{/toegang}
			{if $draad === null}
				<input type="text" id="nieuweTitel" name="titel" class="FormElement TextField" tabindex="1"
							 placeholder="Onderwerp titel" value="{$post_form_titel}" origvalue="{$post_form_titel}"/>
			{/if}
			<div id="berichtPreview" class="bbcodePreview forumBericht"></div>
			<textarea name="forumBericht" id="forumBericht" class="FormElement BBCodeField forumBericht" tabindex="2"
								rows="12"
								origvalue="{$post_form_tekst}">{$post_form_tekst}</textarea>
			<div class="butn">
				<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" class="btn btn-primary"/>
				<input type="button" value="Voorbeeld" id="forumVoorbeeld" class="btn btn-secondary"
							 onclick="window.bbcode.CsrBBPreview('forumBericht', 'berichtPreview');"/>
				{toegang P_LOGGED_IN}
					<input type="button" value="Concept opslaan" id="forumConcept" class="btn btn-secondary"
								 onclick="window.forum.saveConceptForumBericht();"
								 data-url="/forum/concept/{$deel->forum_id}{if isset($draad)}/{$draad->draad_id}{/if}"/>
				{/toegang}
				<div class="float-right">
					{toegang P_LOGGED_IN}
						<a
							href="/fotoalbum/uploaden/fotoalbum/{CsrDelft\model\groepen\LichtingenModel::getHuidigeJaargang()}/Posters"
							target="_blank">Poster opladen</a>
						&nbsp;
						<a href="/groepen/activiteiten/nieuw" class="post popup">Ketzer maken</a>
						&nbsp;
						<a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a>
						&nbsp;
					{/toegang}
				</div>
			</div>

		</form>
	</div>

	<div class="meldingen">
		{if $deel->isOpenbaar()}
			<div id="public-melding">
				<div class="dikgedrukt">Openbaar forum</div>
				Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br/>
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
</div>
