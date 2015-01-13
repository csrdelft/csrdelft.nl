<div id="profiel" {if $profiel->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">
				{$profiel->getPasfotoTag(false)}<br />
				<div class="knopjes">
					{if $profiel->magBewerken()}
						<a href="/profiel/{$profiel->uid}/bewerken" class="btn" title="Bewerk dit profiel">{icon get="bewerken"}</a>
						<a href="/profiel/{$profiel->uid}/voorkeuren" class="btn" title="Pas voorkeuren voor commissies aan">{icon get="report_edit"}</a>
					{/if}
					{if LoginModel::mag('groep:bestuur')}
						<a href="/profiel/{$profiel->uid}/dd" class="btn" title="Wijzig de lidstatus">{icon get="group_edit"}</a>
					{/if}
					<a href="/profiel/{$profiel->uid}/addToGoogleContacts/" class="btn" title="{*if $profiel->isInGoogleContacts()}Er bestaat al een contact met deze naam in je Google-contacts. Klik om te updaten.{else*}Voeg dit profiel toe aan mijn google adresboek{*/if*}"><img src="/plaetjes/knopjes/google.ico" width="16" height="16" alt="tovoegen aan Google contacts"/></a>
					{if LoginModel::mag('P_ADMIN')}
						<a href="/tools/stats.php?uid={$profiel->uid}" class="btn" title="Toon bezoeklog">{icon get="server_chart"}</a>
						{*<a href="/profiel/{$profiel->uid}/wachtwoord" class="btn post confirm prompt" data="wachtwoord=" title="Wachtwoord wijzigen voor {$profiel->getNaam()}">{icon get="resetpassword"}</a>*}
					{/if}
					{if $profiel->status === LidStatus::Noviet AND LoginModel::mag('groep:novcie')}
						<a href="/profiel/{$profiel->uid}/bewerken" class="btn"><img src="/plaetjes/forum/bewerken.png" title="Bewerk dit profiel" alt="bewerken" />Noviet bewerken</a><br />
						{/if}
				</div>
			</div>
			{getMelding()}
			<h1 title="Lid-status: {LidStatus::getDescription($profiel->status)}">
				<div class="status">{LidStatus::getChar($profiel->status)}&nbsp;</div>
				{$profiel->getNaam('volledig')}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div> {$profiel->getNaam('civitas')}<br />
			<div class="label">Lidnummer:</div>
			{if LoginModel::instance()->maySuTo($profiel->getAccount())}
				<a href="/su/{$profiel->uid}/" title="Su naar dit lid">{$profiel->uid}</a>
			{else}
				{$profiel->uid}
			{/if}<br />
			{if $profiel->nickname!=''}<div class="label">Bijnaam:</div> {$profiel->nickname}<br />{/if}
			{if $profiel->duckname!=''}<div class="label">Duckstad-naam:</div> {$profiel->duckname}<br /><br />{/if}
			{if $profiel->voorletters!=''}<div class="label">Voorletters:</div> {$profiel->voorletters}<br />{/if}
			{if $profiel->gebdatum!='0000-00-00'}<div class="label">Geb.datum:</div> {$profiel->gebdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if $profiel->status === LidStatus::Overleden AND $profiel->sterfdatum!='0000-00-00'}<div class="label">Overleden op:</div> {$profiel->sterfdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if ProfielModel::get($profiel->echtgenoot)}
				<div class="label">{if ProfielModel::get($profiel->echtgenoot)->geslacht === Geslacht::Vrouw}Echtgenote{else}Echtgenoot{/if}:</div>
				{ProfielModel::get($profiel->echtgenoot)->getLink('civitas')}<br />
			{/if}
		</div>
	</div>
	{if $profiel->status != LidStatus::Overleden AND ($profiel->adres!='' OR $profiel->o_adres!='')}
		<div class="profielregel">
			<div class="gegevens">
				<div class="gegevenszelf">
					<div class="label">
						{if $profiel->adres!=''}
							<a target="_blank" href="https://maps.google.nl/maps?q={$profiel->adres|urlencode}+{$profiel->woonplaats|urlencode}+{$profiel->land|urlencode} ({if $woonoord != ''}{$profiel->getWoonoord()->getNaam()}{else}{$profiel->getNaam('civitas')}{/if})">
								<img src="/plaetjes/layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
							</a>
						{/if}
					</div>
					<div class="adres">
						{$woonoord}<br />
						{$profiel->adres}<br />
						{$profiel->postcode} {$profiel->woonplaats}<br />
						{$profiel->land}<br />
						{if $profiel->telefoon!=''}{$profiel->telefoon}<br />{/if}
						{if $profiel->mobiel!=''}{$profiel->mobiel}<br />{/if}
					</div>
				</div>
				{if $profiel->isLid()}
					<div class="gegevensouders">
						{if $profiel->o_adres!=''}
							<div class="label">
								<a target="_blank" href="https://maps.google.nl/maps?q={$profiel->o_adres|urlencode}+{$profiel->o_woonplaats|urlencode}+{$profiel->o_land|urlencode} (ouders van {$profiel->getNaam('civitas')})">
									<img src="/plaetjes/layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
								</a>
							</div>
						{/if}
						<div class="adres">
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
				{if $profiel->lidjaar!=0}
					<a href="/ledenlijst?q=lichting:{$profiel->lidjaar}&amp;status=ALL" title="Bekijk de leden van lichting {$profiel->lidjaar}">{$profiel->lidjaar}</a>
				{/if}
				{if !$profiel->isLid() AND $profiel->lidafdatum!='0000-00-00'} tot {$profiel->lidafdatum|substr:0:4}{/if}<br />
				<div class="label">Status:</div> {LidStatus::getDescription($profiel->status)}<br />
				<br />

				{if $profiel->isOudlid()}
					{if $profiel->beroep!=''}<div class="label">Beroep/werk:</div><div class="data">{$profiel->beroep}</div><br />{/if}
				{/if}
				{if $profiel->kring}
					<div class="label">Kring:</div>
					{$profiel->getKringLink()}<br />
				{elseif $profiel->verticale!=0}
					<div class="label">Verticale:</div>
					<a href="/ledenlijst?q=verticale:{$profiel->verticale}">{$profiel->getVerticale()->naam}</a><br />
				{/if}
				{if $profiel->kringcoach}
					<div class="label">Kring:</div>
					{$profiel->getKringLink()}<br />
				{/if}
				{if $profiel->moot}
					<div class="label">Oude moot:</div>
					<a href="/ledenlijst?q=moot:{$profiel->moot}">{$profiel->moot}</a>
				{/if}
			</div>
			<div class="familie">
				{if ProfielModel::get($profiel->patroon) OR $profiel->hasKinderen()}
					<a class="stamboom" href="/leden/stamboom/{$profiel->uid}" title="Stamboom van {$profiel->getNaam()}">
						<img src="/plaetjes/knopjes/stamboom.jpg" alt="Stamboom van {$profiel->getNaam()}" />
					</a>
				{/if}
				{if ProfielModel::get($profiel->patroon)}
					<div class="label">{if ProfielModel::get($profiel->patroon)->geslacht === Geslacht::Vrouw}M{else}P{/if}atroon:</div>
					<div class="data">
						{ProfielModel::get($profiel->patroon)->getLink('civitas')}<br />
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
	<div id="groepen" class="profielregel clear-right">
		<div class="gegevens">
			{$groepen->view()}
			<div class="clear-left"></div>
		</div>
	</div>
	{if ($profiel->isLid() OR (LoginModel::mag('P_LEDEN_MOD') AND ($profiel->soccieSaldo < 0 OR $profiel->maalcieSaldo < 0))) AND (isset($saldografiek) OR $profiel->bankrekening!='')}
		<div class="profielregel">
			<div class="gegevens">
				{if $profiel->bankrekening!=''}
					<div class="label">Bankrekening:</div> {$profiel->bankrekening}
					{if LoginModel::mag('P_MAAL_MOD')}
						<span class="lichtgrijs">({if !$profiel->machtiging}geen {/if}machtiging getekend)</span>
					{/if}
					<br />
				{/if}
				<a name="SocCieSaldo"></a><a name="MaalCieSaldo"></a>
					{if isset($saldografiek)}
					<br />
					{include file='profiel/_saldografiek.tpl'}
				{/if}
			</div>
		</div>
	{/if}

	<div class="profielregel" id="maaltijden">
		<div class="gegevens">
			{if LoginModel::getUid() === $profiel->uid OR LoginModel::mag('P_MAAL_MOD')}
				<div class="label">Recent:</div>
				<ul class="nobullets data">
					{foreach from=$recenteAanmeldingen item=aanmelding}
						<li>{$aanmelding->getMaaltijd()->getTitel()} <span class="lichtgrijs">({$aanmelding->getMaaltijd()->getDatum()|date_format:"%a %e %b"})</span></li>
						{/foreach}
				</ul>
				<br />
				{if $abos}
					<div class="label">Abo's:</div>
					<ul class="nobullets data">
						{foreach from=$abos item=abonnement}
							<li>{$abonnement->getMaaltijdRepetitie()->getStandaardTitel()}</li>
							{/foreach}
					</ul>
				{/if}
				<br />
			{/if}
			<div class="label">Allergie/dieet:</div>
			<div class="data">{strip}
				{if $profiel->eetwens!=''}
					{$profiel->eetwens}
				{else}
					-
				{/if}
				{if LoginModel::getUid() === $profiel->uid}
					&nbsp;<div class="inline" style="position: absolute;"><a href="/corveevoorkeuren" title="Bewerk voorkeuren" class="btn">{icon get="pencil"}</a></div>
					{/if}
			</div>{/strip}
			<br />
			<div class="label">Corvee-<br />voorkeuren:</div>
			<ul class="nobullets data">
				{foreach from=$corveevoorkeuren item=vrk}
					<li>{$vrk->getCorveeRepetitie()->getDagVanDeWeekText()|truncate:2:""} {$vrk->getCorveeRepetitie()->getCorveeFunctie()->naam}</span></li>
					{/foreach}
			</ul>
			<br />
			<div class="label">Kwalificaties:</div>
			<ul class="nobullets data">
				{foreach from=$corveekwalificaties item=kwali}
					<li>{$kwali->getCorveeFunctie()->naam}<span class="lichtgrijs"> (sinds {$kwali->wanneer_toegewezen})</span></li>
					{/foreach}
			</ul>
			<br />
			<div class="label">Corveetaken:</div>
			<ul class="nobullets data">
				{foreach from=$corveetaken item=taak}
					<li>{$taak->getCorveeFunctie()->naam} <span class="lichtgrijs">({$taak->getDatum()|date_format:"%a %e %b"})</span></li>
					{/foreach}
			</ul>
			<br />
			<div class="label">Corveepunten:</div>
			<div class="data">{$corveepunten}{if $corveebonus > 0}+{/if}{if $corveebonus != 0}{$corveebonus}{/if}</div>
		</div>
	</div>

	{if LoginModel::getUid() === $profiel->uid}
		<div class="profielregel" id="agenda">
			<div class="gegevens" id="agenda_gegevens">
				<div class="label">ICal-feed:</div>
				<div class="data">
					{if $profiel->getAccount()->hasPrivateToken()}
						<a href="{$profiel->getAccount()->getICalLink()}">
							<img src="/plaetjes/knopjes/ical.gif" /> Persoonlijke ICal-feed agenda
						</a>
					{/if}
					<small>Gebruikt dezelfde private token als het forum (zie hieronder)</small>
				</div>
				<br />
			</div>
		</div>
	{/if}

	{if $forumpostcount > 0 OR LoginModel::getUid() === $profiel->uid}
		<div class="profielregel" id="forum">
			<div class="gegevens" id="forum_gegevens">
				{if LoginModel::getUid() === $profiel->uid}
					<div class="label">RSS-feed:</div>
					<div class="data">
						{if $profiel->getAccount()->hasPrivateToken()}
							<a href="{$profiel->getAccount()->getRssLink()}">
								{icon get='feed'} Persoonlijke RSS-feed forum
							</a>
						{/if}
						<a name="tokenaanvragen" class="btn" href="/profiel/{$profiel->uid}/resetPrivateToken#forum">Nieuwe aanvragen</a>
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
							{foreach from=ForumPostsModel::instance()->getRecenteForumPostsVanLid($profiel->uid, (int) LidInstellingen::get('forum', 'draden_per_pagina')) item=post}
								<tr>
									<td><a href="/forum/reactie/{$post->post_id}#{$post->post_id}" title="{htmlspecialchars($post->tekst)}"{if $post->getForumDraad()->onGelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>{$post->getForumDraad()->titel|truncate:75}</a></td>
									<td>
										{if LidInstellingen::get('forum', 'datumWeergave') === 'relatief'}
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
	{if $boeken OR LoginModel::getUid() === $profiel->uid OR $gerecenseerdeboeken}
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
				{if LoginModel::getUid() === $profiel->uid}
					<a class="btn" href="/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
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
	{if LoginModel::mag('P_ADMIN,groep:bestuur,groep:novcie') AND $profiel->status === LidStatus::Noviet AND $profiel->kgb!=''}
		<div class="profielregel" id="novcieopmerking">
			<div style="cursor: pointer;" onclick="$('#novcie_gegevens').toggle();">NovCie-Opmerking &raquo;</div>
			<div class="gegevens verborgen" id="novcie_gegevens">{$profiel->kgb|bbcode}</div>
		</div>
	{/if}
	{if LoginModel::mag('P_LEDEN_MOD')}
		<div class="profielregel" id="changelog">
			<div class="gegevens">
				<div style="cursor: pointer;" onclick="$('#changelog_gegevens').toggle();
						this.remove()">Bewerklog &raquo;</div>
				<div class="verborgen" id="changelog_gegevens">
					{$profiel->changelog|bbcode}
				</div>
			</div>
		</div>
	{/if}
</div>