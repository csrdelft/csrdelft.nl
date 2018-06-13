<div id="profiel" {if $profiel->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="float-right">
				<div class="pasfoto float-left">{$profiel->getPasfotoTag(false)}</div>
				<div class="knopjes">
					{*<a href="/geolocation/map/{$profiel->uid}" class="btn" title="Huidige locatie op kaart tonen">{icon get="map"}</a>*}
					<a href="/profiel/{$profiel->uid}/addToGoogleContacts/" class="btn btn-light" title="{if $profiel->isInGoogleContacts()}Dit profiel opdateren in mijn google adresboek{else}Dit profiel toevoegen aan mijn google adresboek{/if}">
						<img src="/images/google.ico" width="16" height="16" alt="tovoegen aan Google contacts"/>
					</a>
					{if $profiel->magBewerken()}
						<a href="/profiel/{$profiel->uid}/bewerken" class="btn btn-light" title="Bewerk dit profiel">{icon get="pencil"}</a>
						<a href="/profiel/{$profiel->uid}/voorkeuren" class="btn btn-light" title="Pas voorkeuren voor commissies aan">{icon get="report_edit"}</a>
						<a href="/toestemming" class="btn btn-light" title="Pas toestemming aan">{icon get="lock_edit"}</a>
					{/if}
					{if mag('P_ADMIN') OR is_ingelogd_account($profiel->uid)}
						{if CsrDelft\model\security\AccountModel::existsUid($profiel->uid)}
							<a href="/account/{$profiel->uid}/bewerken" class="btn btn-light" title="Inloggegevens bewerken">{icon get="key"}</a>
						{else}
                            {toegang P_ADMIN}
							<a href="/account/{$profiel->uid}/aanmaken" class="btn btn-light" title="Account aanmaken">{icon get="key_delete" hover="key_add"}</a>
                            {/toegang}
						{/if}
						{toegang P_ADMIN}
							<a href="/tools/stats.php?uid={$profiel->uid}" class="btn btn-light" title="Toon bezoeklog">{icon get="server_chart"}</a>
						{/toegang}
					{/if}
				</div>
			</div>
			{getMelding()}
			<h1 title="Lid-status: {CsrDelft\model\entity\LidStatus::getDescription($profiel->status)}">
				{if CsrDelft\model\entity\LidStatus::getChar($profiel->status)!=''}<span class="status">{CsrDelft\model\entity\LidStatus::getChar($profiel->status)}&nbsp;</span>{/if}
				{$profiel->getNaam('volledig')}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="gegevens">
			<div class="label">Naam:</div><div class="data">{$profiel->getNaam('civitas')}</div>
			<div class="label">Lidnummer:</div><div class="data">
				{if App\Models\Account::existsByUid($profiel->uid) AND Auth::maySuTo($profiel->getAccount())}
					<a href="/account/{$profiel->uid}/su" title="Su naar dit lid">{$profiel->uid}</a>
				{else}
					{$profiel->uid}
				{/if}</div>
			{if $profiel->nickname!=''}<div class="label">Bijnaam:</div><div class="data">{$profiel->nickname}</div>{/if}
			{if $profiel->duckname!=''}<div class="label">Duckstad-naam:</div><div class="data">{$profiel->duckname}</div>{/if}
			<br />
			{if $profiel->voorletters!=''}<div class="label">Voorletters:</div><div class="data">{$profiel->voorletters}</div>{/if}
			{if $profiel->gebdatum!='0000-00-00'}<div class="label">Geb.datum:</div><div class="data">{$profiel->gebdatum|date_format:"%d-%m-%Y"}</div>{/if}
			{if $profiel->status === CsrDelft\model\entity\LidStatus::Overleden AND $profiel->sterfdatum!='0000-00-00'}<div class="label">Overleden op:</div><div class="data">{$profiel->sterfdatum|date_format:"%d-%m-%Y"}</div>{/if}
			{if CsrDelft\model\ProfielModel::get($profiel->echtgenoot)}
				<div class="label">{if CsrDelft\model\ProfielModel::get($profiel->echtgenoot)->geslacht === CsrDelft\model\entity\Geslacht::Vrouw}Echtgenote{else}Echtgenoot{/if}:</div>
				<div class="data">{CsrDelft\model\ProfielModel::get($profiel->echtgenoot)->getLink('civitas')}</div>
			{/if}
		</div>
	</div>

	{if $profiel->status != CsrDelft\model\entity\LidStatus::Overleden AND ($profiel->adres!='' OR $profiel->o_adres!='')}
		<div class="profielregel">
			<div class="gegevens">
				<div class="half">
					<div class="label">
						{if $profiel->adres!=''}
							<a target="_blank" href="https://maps.google.nl/maps?q={$profiel->adres|urlencode}+{$profiel->woonplaats|urlencode}+{$profiel->land|urlencode}" title="Open kaart" class="lichtgrijs fa fa-map-marker fa-5x"></a>
						{/if}
					</div>
					<div class="data">
						{$woonoord}<br />
						{$profiel->adres}<br />
						{$profiel->postcode} {$profiel->woonplaats}<br />
						{$profiel->land}<br />
						{if $profiel->telefoon!=''}{$profiel->telefoon}<br />{/if}
						{if $profiel->mobiel!=''}{$profiel->mobiel}<br />{/if}
					</div>
				</div>
				{if $profiel->isLid()}
					<div class="half">
						{if $profiel->o_adres!=''}
							<div class="label">
								<a target="_blank" href="https://maps.google.nl/maps?q={$profiel->o_adres|urlencode}+{$profiel->o_woonplaats|urlencode}+{$profiel->o_land|urlencode}" title="Open kaart" class="lichtgrijs fa fa-map-marker fa-5x"></a>
							</div>
						{/if}
						<div class="data">
							{if $profiel->o_adres!=''}
								<strong>Ouders:</strong><br />
								{$profiel->o_adres}<br />
								{$profiel->o_postcode} {$profiel->o_woonplaats}<br />
								{$profiel->o_land}<br />
								{$profiel->o_telefoon}
							{/if}
						</div>
					</div>
				{/if}
				<div class="clear-left"></div>
			</div>
		</div>
	{/if}

	<div class="profielregel">
		<div class="gegevens">
			{foreach from=$profiel->getContactgegevens() key=key item=contact}
				{if $contact != ''}
					<div class="label">{$key}:</div>
					{$contact}<br />
				{/if}
			{/foreach}
		</div>
	</div>

	<div class="profielregel">
		<div class="gegevens">
			<div class="half">
				{if $profiel->studie!=''}
					<div class="label">Studie:</div> <div class="data">{$profiel->studie}</div>

					<div class="label">Studie sinds:</div> {$profiel->studiejaar}<br />
				{/if}
				<div class="label">Lid sinds:</div>
				{if $profiel->lidjaar>0}
					<a href="/ledenlijst?q=lichting:{$profiel->lidjaar}&amp;status=ALL" title="Bekijk de leden van lichting {$profiel->lidjaar}">{$profiel->lidjaar}</a>
				{/if}
				{if !$profiel->isLid() AND $profiel->lidafdatum!='0000-00-00'} tot {$profiel->lidafdatum|substr:0:4}{/if}<br />
				<div class="label">Status:</div> {CsrDelft\model\entity\LidStatus::getDescription($profiel->status)}<br />
				<br />
				{if $profiel->isOudlid()}
					{if $profiel->beroep!=''}<div class="label">Beroep/werk:</div><div class="data">{$profiel->beroep}</div><br />{/if}
				{/if}
			</div>
			<div class="half">
				{if CsrDelft\model\ProfielModel::get($profiel->patroon) OR $profiel->hasKinderen()}
					<a class="float-right lichtgrijs fa fa-tree fa-3x" href="/leden/stamboom/{$profiel->uid}" title="Stamboom van {$profiel->getNaam()}"></a>
				{/if}
				{if CsrDelft\model\ProfielModel::get($profiel->patroon)}
					<div class="label">{if CsrDelft\model\ProfielModel::get($profiel->patroon)->geslacht === CsrDelft\model\entity\Geslacht::Vrouw}M{else}P{/if}atroon:</div>
					<div class="data">
						{CsrDelft\model\ProfielModel::get($profiel->patroon)->getLink('civitas')}<br />
					</div>
				{/if}
				{if $profiel->hasKinderen()}
					<div class="label">Kinderen:</div>
					<div class="data">
						{foreach from=$profiel->getKinderen() item=kind name=kinderen}
							{$kind->getLink('civitas')}<br />
						{/foreach}
					</div>
				{/if}
			</div>
			<div class="clear-left"></div>
		</div>
	</div>

	<div class="profielregel clear-right">
		<div class="gegevens">
			<div class="half">
				{if $profiel->verticale!=''}
					<div class="label">Verticale:</div>
					<div class="data"><a href="/ledenlijst?q=verticale:{$profiel->verticale}">{$profiel->getVerticale()->naam}</a></div>
				{/if}
				{if $profiel->moot}
					<div class="label">Oude moot:</div>
					<div class="data"><a href="/ledenlijst?q=moot:{$profiel->moot}">{$profiel->moot}</a></div>
				{/if}
			</div>
			<div class="half">
				{if $kring}
					<div class="label">Kring:</div>
					<div class="data">{$kring}</div>
				{/if}
			</div>
			<div class="clear-left"></div>
		</div>
	</div>

	<div class="profielregel clear-right">
		<div class="gegevens">
			<div class="half">
				{$besturen}
				{$commissies}
				{$onderverenigingen}
				{$groepen}
			</div>
			<div class="half">
				{$werkgroepen}
				{if mag('P_LEDEN_MOD') OR is_ingelogd_account($profiel->uid)}
				<div class="label">&nbsp;</div><a class="btn btn-primary" onclick="$(this).remove();
						$('#meerGroepenContainer').slideDown();" tabindex="0">Toon activiteiten</a>
			</div>
			<div class="clear-left"></div>
			<div id="meerGroepenContainer" style="display: none;">
				<div class="half">
					{$ketzers}
				</div>
				<div class="half">
					{$activiteiten}
				</div>
				<div class="clear-left"></div>
				{/if}
			</div>
		</div>
	</div>

	{if ($profiel->isLid() OR (mag('P_LEDEN_MOD') AND ($profiel->getCiviSaldo() < 0))) AND (isset($saldografiek) OR $profiel->bankrekening!='')}
		<div class="profielregel">
			<div class="gegevens">
				{if $profiel->bankrekening!=''}
					<div class="label">Bankrekening:</div> {$profiel->bankrekening}
					{toegang P_MAAL_MOD}
						<span class="lichtgrijs">({if !$profiel->machtiging}geen {/if}machtiging getekend)</span>
					{/toegang}
				{/if}
				<div class="clear-left"></div>
				{if mag('P_MAAL_MOD') OR is_ingelogd_account($profiel->uid)}
					<a id="CiviSaldo"></a>
					<div class="label">Saldohistorie:</div>
					{foreach from=$bestellinglog item=bestelling}
						<div class="data {cycle values="donker,licht"}">
							<span>{implode(", ", $bestelling->inhoud)}</span>
							<span class="float-right">{$bestelling->totaal|bedrag}</span>
							<span class="float-right lichtgrijs bestelling-moment">({$bestelling->moment|date_format:"%D"}) </span>
						</div>
					{/foreach}
					<div class="data">
						<a href="{$bestellingenlink}">Meer &#187;</a>
					</div>
				{/if}

				{if isset($saldografiek)}
					<div class="clear-left"></div>
					<div class="label">Saldografiek:</div>
					<div class="clear-left"></div>
                    {include file='profiel/_saldografiek.tpl'}
				{/if}
			</div>
		</div>
	{/if}

	<div class="profielregel" id="maaltijden">
		<div class="gegevens">
			<div class="label">Allergie/dieet:</div>
			<div class="data">
				{strip}
					{if $profiel->eetwens!=''}
						{$profiel->eetwens}
					{else}
						-
					{/if}
					{if is_ingelogd_account($profiel->uid)}
						&nbsp;<div class="inline" style="position: absolute;"><a href="/corveevoorkeuren" title="Bewerk voorkeuren" class="btn">{icon get="pencil"}</a></div>
					{/if}
				</div>
			{/strip}
			<br />
			{if mag('P_MAAL_MOD') OR is_ingelogd_account($profiel->uid)}
				{if isset($abos)}
					<div class="label">Abo's:</div>
					<ul class="nobullets data">
						{foreach from=$abos item=abonnement}
							<li>{$abonnement->maaltijd_repetitie->standaard_titel}</li>
							{/foreach}
					</ul>
				{/if}
				<div class="clear"></div>
				<div class="half">
					<div class="label">Corvee-<br />voorkeuren:</div>
					<ul class="nobullets data">
						{foreach from=$corveevoorkeuren item=vrk}
							<li>{$vrk->getCorveeRepetitie()->getDagVanDeWeekText()|truncate:2:""} {$vrk->getCorveeRepetitie()->getCorveeFunctie()->naam}</li>
							{/foreach}
					</ul>
				</div>
				<div class="half">
					<div class="label">Recent:</div>
					<ul class="nobullets data">
						{foreach from=$recenteAanmeldingen item=aanmelding}
							<li>{$aanmelding->maaltijd->getTitel()} <span class="lichtgrijs">({$aanmelding->maaltijd->datum|date_format:"%a %e %b"})</span></li>
							{/foreach}
					</ul>
				</div>
				<div class="clear"></div>
				<div class="half">
					<div class="label">Corveepunten:</div>
					<div class="data">{$corveepunten}{if $corveebonus > 0}+{/if}{if $corveebonus != 0}{$corveebonus}{/if}</div>
				</div>
				<div class="half">
					<div class="label">Kwalificaties:</div>
					<ul class="nobullets data">
						{foreach from=$corveekwalificaties item=kwali}
							<li>{$kwali->getCorveeFunctie()->naam}<span class="lichtgrijs"> (sinds {$kwali->wanneer_toegewezen})</span></li>
							{/foreach}
					</ul>
				</div>
				<div class="clear"></div>
				<div class="label">Corveetaken:</div>
				<ul class="nobullets data">
					{foreach from=$corveetaken item=taak}
						<li>{$taak->getCorveeFunctie()->naam} <span class="lichtgrijs">({$taak->datum|date_format:"%a %e %b"})</span></li>
						{/foreach}
				</ul>
			{/if}
			<br />
		</div>
	</div>

	{if is_ingelogd_account($profiel->uid)}
		<div class="profielregel" id="agenda">
			<div class="gegevens" id="agenda_gegevens">
				<div class="label">Persoonlijke<br />ICal-feed:</div>
				<div class="data">
					{if $profiel->getAccount()->hasPrivateToken()}
						<input type="text" value="{$profiel->getAccount()->getICalLink()}" size="50" onclick="this.setSelectionRange(0, this.value.length);" readonly />
					{/if}
					&nbsp; <small>Gebruikt dezelfde private token als het forum (zie hieronder)</small>
				</div>
				<br />
			</div>
		</div>
	{/if}

	{if $forumpostcount > 0 OR is_ingelogd_account($profiel->uid)}
		<div class="profielregel" id="forum">
			<div class="gegevens" id="forum_gegevens">
				{if is_ingelogd_account($profiel->uid)}
					<div class="label">Persoonlijk<br />RSS-feed:</div>
					<div class="data">
						{if $profiel->getAccount()->hasPrivateToken()}
							<input type="text" value="{$profiel->getAccount()->getRssLink()}" size="50" onclick="this.setSelectionRange(0, this.value.length);" readonly />
						{/if}
						&nbsp; <a name="tokenaanvragen" class="btn" href="/profiel/{$profiel->uid}/resetPrivateToken">Nieuwe aanvragen</a>
					</div>
					<br />
				{/if}
				{if $forumpostcount > 0}
					<div class="label"># bijdragen:</div>
					<div class="data">
						{$forumpostcount} bericht{if $forumpostcount> 1 }en{/if}.
					</div>
					<div class="label">Recent:</div>
					<div class="data">
						<table id="recenteForumberichten">
							{foreach from=CsrDelft\model\forum\ForumPostsModel::instance()->getRecenteForumPostsVanLid($profiel->uid, (int) CsrDelft\model\LidInstellingenModel::get('forum', 'draden_per_pagina')) item=post}
								<tr>
									<td><a href="/forum/reactie/{$post->post_id}#{$post->post_id}" title="{htmlspecialchars($post->tekst)}"{if $post->getForumDraad()->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>{$post->getForumDraad()->titel|truncate:75}</a></td>
									<td>
										{if CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief'}
											{$post->datum_tijd|reldate}
										{else}
											{$post->datum_tijd}
										{/if}
									</td>
								</tr>
							{foreachelse}
								<tr><td>Geen bijdragen</td></tr>
							{/foreach}
						</table>
					</div>
				{/if}
			</div>
		</div>
	{/if}

	{if $boeken OR is_ingelogd_account($profiel->uid) OR $gerecenseerdeboeken}
		<div class="profielregel boeken" id="boeken">
			<div class="gegevens">
				{if $boeken}
					<div class="label">Boeken:</div>
					<ul class="nobullets data">
						{foreach from=$boeken item=boek}
							<li>
								<a href="/bibliotheek/boek/{$boek.id}" title="Boek: {$boek.titel|escape:'html'}">
									<span title="{$boek.status} boek" class="boekindicator {$boek.status}">•</span><span class="titel">{$boek.titel|escape:'html'}</span><span class="auteur">{$boek.auteur|escape:'html'}</span>
								</a>
							</li>
						{foreachelse}
							<li>Geen boeken</li>
							{/foreach}
					</ul>
				{/if}
				{if is_ingelogd_account($profiel->uid)}
					<a class="btn" href="/bibliotheek/nieuwboek">{icon get="book_add"} Nieuw boek</a>
					<br />
				{/if}
				{if $gerecenseerdeboeken}
					<br />
					<div class="label">Boekrecensies:</div>
					<ul class="nobullets data">
						{foreach from=$gerecenseerdeboeken item=boek}
							<li>
								<a href="/bibliotheek/boek/{$boek.id}" title="Boek: {$boek.titel|escape:'html'}">
									<span title="{$boek.status} boek" class="boekindicator {$boek.status}">•</span><span class="titel">{$boek.titel|escape:'html'}</span><span class="auteur">{$boek.auteur|escape:'html'}</span>
								</a>
							</li>
						{foreachelse}
							<li>Geen boeken</li>
							{/foreach}
					</ul>
				{/if}
			</div>
		</div>
	{/if}

	<div class="profielregel fotos" id="fotos">
		<div class="gegevens">
			<div class="label">Fotoalbum:</div>
			<div>
				{if empty($fotos)}
					Er zijn geen foto's gevonden met {$profiel->getNaam('civitas')} erop.
				{else}
					{foreach from=$fotos item=foto}
						{$foto->view()}
					{/foreach}
					<a class="btn" href="/fotoalbum/{$profiel->uid}">Toon alle foto's</a>
				{/if}
			</div>
		</div>
	</div>

	{toegang 'P_ADMIN,bestuur,commissie:NovCie'}
	{if $profiel->status === CsrDelft\model\entity\LidStatus::Noviet AND $profiel->kgb!=''}
		<div class="profielregel" id="novcieopmerking">
			<div style="cursor: pointer;" onclick="$('#novcie_gegevens').toggle();">NovCie-Opmerking &raquo;</div>
			<div class="gegevens verborgen" id="novcie_gegevens">{$profiel->kgb|bbcode}</div>
		</div>
	{/if}
	{/toegang}

	{toegang P_LEDEN_MOD}
		<div class="profielregel" id="changelog">
			<div class="gegevens">
				<div style="cursor: pointer;" onclick="$('#changelog_gegevens').toggle();
						this.remove()">Bewerklog &raquo;</div>
				<div class="verborgen" id="changelog_gegevens">
					{$profiel->changelog|bbcode}
				</div>
			</div>
		</div>
	{/toegang}

</div>
