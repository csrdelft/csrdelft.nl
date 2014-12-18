<div id="profiel" {if $profiel->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">
				{$profiel->getUid()|pasfoto}<br />
				<div class="knopjes">
					{if $profiel->magBewerken()}
						<a href="/profiel/{$profiel->getUid()}/bewerken" class="btn round" title="Bewerk dit profiel">{icon get="bewerken"}</a>
						<a href="/profiel/{$profiel->getUid()}/voorkeuren" class="btn round" title="Pas voorkeuren voor commissies aan">{icon get="report_edit"}</a>
					{/if}
					{if $isLidMod}
						<a href="/profiel/{$profiel->getUid()}/wijzigstatus" class="btn round" title="Wijzig de lidstatus">{icon get="group_edit"}</a>
					{/if}
					{if $isBestuur}
						<a href="/profiel/{$profiel->getUid()}/dd" class="btn round" title="Wijzig de lidstatus">{icon get="group_edit"}</a>
					{/if}
					<a href="/profiel/{$profiel->getUid()}/addToGoogleContacts/" class="btn round" title="{*if $profiel->isInGoogleContacts()}Er bestaat al een contact met deze naam in je Google-contacts. Klik om te updaten.{else*}Voeg dit profiel toe aan mijn google adresboek{*/if*}"><img src="/plaetjes/knopjes/google.ico" width="16" height="16" alt="tovoegen aan Google contacts"/></a>
						{if $isAdmin}
						<br />
						<a href="/tools/stats.php?uid={$profiel->getUid()}" class="btn round" title="Toon bezoeklog">{icon get="server_chart"}</a>
						<a href="/profiel/{$profiel->getUid()}/wachtwoord" class="btn round" title="Reset wachtwoord voor {$profiel->getNaam()}" onclick="return confirm('Weet u zeker dat u het wachtwoord van deze gebruiker wilt resetten?')">{icon get="resetpassword"}</a>
					{/if}
					{if $profiel->getStatus()=='S_NOVIET' AND LoginModel::mag('groep:novcie')}
						<a href="/profiel/{$profiel->getUid()}/novietBewerken" class="btn round"><img src="/plaetjes/forum/bewerken.png" title="Bewerk dit profiel" alt="bewerken" />Noviet bewerken</a><br />
						{/if}
				</div>
			</div>
			{getMelding()}
			<h1 title="Lid-status: {$profiel->getStatus()->getDescription()}">
				<div class="status">{if !$profiel->isLid()}{$profiel->getStatus()->getChar()}{/if}&nbsp;</div>
				{$profiel->getNaam('volledig', 'plain')}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div> {$profiel->getNaamLink('civitas', 'plain')}<br />
			<div class="label">Lidnummer:</div>
			{if LoginModel::instance()->maySuTo($profiel->getLid())}
				<a href="/su/{$profiel->getUid()}/" title="Su naar dit lid">{$profiel->getUid()}</a>
			{else}
				{$profhtml.uid}
			{/if}<br />
			{if $profhtml.nickname!=''}<div class="label">Bijnaam:</div> {$profhtml.nickname}<br />{/if}
			{if $profhtml.duckname!=''}<div class="label">Duckstad-naam:</div> {$profhtml.duckname}<br /><br />{/if}
			{if $profhtml.voorletters!=''}<div class="label">Voorletters:</div> {$profhtml.voorletters}<br />{/if}
			{if $profhtml.gebdatum!='0000-00-00'}<div class="label">Geb.datum:</div> {$profhtml.gebdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if $profiel->getStatus()=='S_OVERLEDEN' AND $profhtml.sterfdatum!='0000-00-00'}<div class="label">Overleden op:</div> {$profhtml.sterfdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if $profiel->getEchtgenoot() instanceof Lid}
				<div class="label">{if $profiel->getEchtgenoot()->getGeslacht()=='v'}Echtgenote{else}Echtgenoot{/if}:</div>
				{$profiel->getEchtgenoot()->getNaamLink('civitas', 'link')}<br />
			{/if}
		</div>
	</div>
	{if $profiel->getStatus()!='S_OVERLEDEN' AND ($profhtml.adres!='' OR $profhtml.o_adres!='')}
		<div class="profielregel">
			<div class="gegevens">
				<div class="gegevenszelf">
					<div class="label">
						{if $profhtml.adres!=''}
							<a target="_blank" href="http://maps.google.nl/maps?q={$profhtml.adres|urlencode}+{$profhtml.woonplaats|urlencode}+{$profhtml.land|urlencode} ({if $profhtml.woonoord!=''}{$profiel->getWoonoord()->getNaam()}{else}{$profiel->getNaamLink('civitas', 'plain')}{/if})">
								<img src="/plaetjes/layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
							</a>
						{/if}
					</div>
					<div class="adres">
						{$profhtml.woonoord}<br />
						{$profhtml.adres}<br />
						{$profhtml.postcode} {$profhtml.woonplaats}<br />
						{$profhtml.land}<br />
						{if $profhtml.telefoon!=''}{$profhtml.telefoon}<br />{/if}
						{if $profhtml.mobiel!=''}{$profhtml.mobiel}<br />{/if}
					</div>
				</div>
				{if $profiel->isLid()}
					<div class="gegevensouders">
						{if $profhtml.o_adres!=''}
							<div class="label">
								<a target="_blank" href="http://maps.google.nl/maps?q={$profhtml.o_adres|urlencode}+{$profhtml.o_woonplaats|urlencode}+{$profhtml.o_land|urlencode} (ouders van {$profiel->getNaamLink('civitas', 'plain')})">
									<img src="/plaetjes/layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
								</a>
							</div>
						{/if}
						<div class="adres">
							{if $profhtml.o_adres!=''}
								<strong>Ouders:</strong><br />
								{$profhtml.o_adres}<br />
								{$profhtml.o_postcode} {$profhtml.o_woonplaats}<br />
								{$profhtml.o_land}<br />
								{$profhtml.o_telefoon}
							{/if}
						</div>
					</div>
				{/if}
				<div class="clear-left"></div>
			</div>
		</div>
	{/if}
	{if count($profiel->getContactgegevens())>0}
		<div class="profielregel">
			<div class="gegevens">
				{foreach from=$profiel->getContactgegevens() key="key" item="contact"}
					{if in_array($key, array('website', 'linkedin'))}
						<div class="label">{$key|ucfirst}:</div>
						<a target="_blank" href="{$contact|escape:'html'}">{$contact|truncate:60|escape:'htmlall'}</a>
					{elseif $key=='email'}
						<div class="label">Email:</div>
						<a href="mailto:{$contact|escape:'html'}">{$contact|escape:'htmlall'}</a>
					{elseif $key=='jid'}
						<div class="label">Jabber/GTalk:</div>
						{$contact|escape:'htmlall'}
					{elseif in_array($key, array('msn', 'icq'))}
						<div class="label">{$key|upper}:</div>
						{$contact|escape:'htmlall'}
					{else}
						<div class="label">{$key|ucfirst}:</div>
						{$contact|escape:'htmlall'}
					{/if}
					<br />
				{/foreach}

			</div>
		</div>
	{/if}
	<div class="profielregel">
		<div class="gegevens">
			<div class="half">
				{if $profhtml.studie!=''}
					<div class="label">Studie:</div> <div class="data">{$profhtml.studie}</div>

					<div class="label">Studie sinds:</div> {$profhtml.studiejaar}<br />
				{/if}
				<div class="label">Lid sinds:</div>
				{if $profhtml.lidjaar!=0}
					<a href="/ledenlijst?q=lichting:{$profhtml.lidjaar}&amp;status=ALL" title="Bekijk de leden van lichting {$profhtml.lidjaar}">{$profhtml.lidjaar}</a>
				{/if}
				{if !$profiel->isLid() AND $profhtml.lidafdatum!='0000-00-00'} tot {$profhtml.lidafdatum|substr:0:4}{/if}<br />
				<div class="label">Status:</div> {$profiel->getStatus()->getDescription()}<br />
				<br />

				{if $profiel->isOudlid()}
					{if $profhtml.beroep!=''}<div class="label">Beroep/werk:</div><div class="data">{$profhtml.beroep}</div><br />{/if}
				{/if}
				{if $profhtml.kring!=0}
					<div class="label">Kring:</div>
					{$profiel->getKring(true)}
					<br />
				{elseif $profhtml.verticale!=0}
					<div class="label">Verticale:</div>
					<a href="/ledenlijst?q=verticale:{$profiel->getVerticale()->letter}">{$profiel->getVerticale()->naam}</a><br />
				{/if}
				{if $profhtml.moot!=0}
					<div class="label">Oude moot:</div>
					<a href="/ledenlijst?q=moot:{$profhtml.moot}">{$profhtml.moot}</a>
				{/if}
			</div>
			<div class="familie">
				{if $profiel->getPatroon() instanceof Lid OR $profiel->getKinderen()|@count > 0}
					<a class="stamboom" href="/leden/stamboom/{$profiel->getUid()}" title="Stamboom van {$profiel->getNaam()}">
						<img src="/plaetjes/knopjes/stamboom.jpg" alt="Stamboom van {$profiel->getNaam()}" />
					</a>
				{/if}
				{if $profiel->getPatroon() instanceof Lid}
					<div class="label">{if $profiel->getPatroon()->getGeslacht()=='v'}M{else}P{/if}atroon:</div>
					<div class="data">
						{$profiel->getPatroon()->getNaamLink('civitas', 'link')}<br />
					</div>
				{/if}
				{if $profiel->getKinderen()|@count > 0}
					<div class="label">Kinderen:</div>
					<div class="data">
						{foreach from=$profiel->getKinderen() item=kind name=kinderen}
							{$kind->getNaamLink('civitas', 'link')}<br />
						{/foreach}
					</div>
				{/if}
			</div>
			<div class="clear-left"></div>
		</div>
	</div>
	<div id="groepen" class="profielregel clear-right">
		<div class="gegevens">
			{$profhtml.groepen->view()}
			<div class="clear-left"></div>
		</div>
	</div>
	{if ($profiel->isLid() OR (LoginModel::mag('P_LEDEN_MOD') AND ($profhtml.soccieSaldo < 0 OR $profhtml.maalcieSaldo < 0))) AND (isset($saldografiek) OR $profhtml.bankrekening!='')}
		<div class="profielregel">
			<div class="gegevens">
				{if $profhtml.bankrekening!=''}
					<div class="label">Bankrekening:</div> {$profhtml.bankrekening}
					{if LoginModel::mag('P_MAAL_MOD')}
						<span class="lichtgrijs">({if $profhtml.machtiging=='nee'}geen {/if}machtiging getekend)</span>
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
			{if LoginModel::getUid()==$profhtml.uid OR LoginModel::mag('P_MAAL_MOD')}
				<div class="label">Recent:</div>
				<ul class="nobullets data">
					{foreach from=$profhtml.recenteAanmeldingen item=aanmelding}
						<li>{$aanmelding->getMaaltijd()->getTitel()} <span class="lichtgrijs">({$aanmelding->getMaaltijd()->getDatum()|date_format:"%a %e %b"})</span></li>
						{/foreach}
				</ul>
				<br />
				{if $profhtml.abos}
					<div class="label">Abo's:</div>
					<ul class="nobullets data">
						{foreach from=$profhtml.abos item=abonnement}
							<li>{$abonnement->getMaaltijdRepetitie()->getStandaardTitel()}</li>
							{/foreach}
					</ul>
				{/if}
				<br />
			{/if}
			<div class="label">Allergie/dieet:</div>
			<div class="data">{strip}
				{if $profhtml.eetwens!=''}
					{$profhtml.eetwens}
				{else}
					-
				{/if}
				{if LoginModel::getUid()==$profhtml.uid}
					&nbsp;<div class="inline" style="position: absolute;"><a href="/corveevoorkeuren" title="Bewerk voorkeuren" class="btn round">{icon get="pencil"}</a></div>
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

	{if LoginModel::getUid()==$profiel->getUid()}
		<div class="profielregel" id="agenda">
			<div class="gegevens" id="agenda_gegevens">
				<div class="label">ICal-feed:</div>
				<div class="data">
					{if $profhtml.rssToken!=''}
						<a href="{$profiel->getICalLink()}">
							<img src="/plaetjes/knopjes/ical.gif" /> Persoonlijke ICal-feed agenda
						</a>
					{/if}
					<small>Gebruikt dezelfde private token als het forum (zie hieronder)</small>
				</div>
				<br />
			</div>
		</div>
	{/if}

	{if $profiel->getForumPostCount() > 0 OR LoginModel::getUid()==$profiel->getUid()}
		<div class="profielregel" id="forum">
			<div class="gegevens" id="forum_gegevens">
				{if LoginModel::getUid()==$profiel->getUid()}
					<div class="label">RSS-feed:</div>
					<div class="data">
						{if $profhtml.rssToken!=''}
							<a href="{$profiel->getRssLink()}">
								{icon get='feed'} Persoonlijke RSS-feed forum
							</a>
						{/if}
						<a name="tokenaanvragen" class="btn" href="/profiel/{$profiel->getUid()}/rssToken#forum">Nieuwe aanvragen</a>
					</div>
					<br />
				{/if}
				{if $profiel->getForumPostCount() > 0}
					<div class="label"># bijdragen:</div>
					<div class="data">
						{$profiel->getForumPostCount()} bericht{if $profiel->getForumPostCount()> 1 }en{/if}.
					</div>
					<div class="label">Recent:</div>
					<div class="data">
						<table id="recenteForumberichten">
							{assign var=posts_draden value=$profiel->getRecenteForumberichten()}
							{foreach from=$posts_draden[0] item=post}
								<tr>
									<td><a href="/forum/reactie/{$post->post_id}#{$post->post_id}" title="{htmlspecialchars($post->tekst)}"{if $posts_draden[1][$post->draad_id]->onGelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>{$posts_draden[1][$post->draad_id]->titel|truncate:75|escape:'html'}</a></td>
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
	{if $boeken OR LoginModel::getUid()==$profhtml.uid OR $gerecenseerdeboeken}
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
				{if LoginModel::getUid()==$profhtml.uid}
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
	{if LoginModel::mag('P_ADMIN,R_BESTUUR,groep:novcie') AND $profiel->getStatus()=='S_NOVIET' AND $profhtml.kgb!=''}
		<div class="profielregel" id="novcieopmerking">
			<div style="cursor: pointer;" onclick="$('#novcie_gegevens').toggle();">NovCie-Opmerking &raquo;</div>
			<div class="gegevens verborgen" id="novcie_gegevens">{$profhtml.kgb|bbcode}</div>
		</div>
	{/if}
	{if ($isAdmin OR $isLidMod) AND $profhtml.changelog!=''}
		<div class="profielregel" id="changelog">
			<div class="gegevens">
				<div style="cursor: pointer;" onclick="$('#changelog_gegevens').toggle();
						this.remove()">Bewerklog &raquo;</div>
				<div class="verborgen" id="changelog_gegevens">
					{$profhtml.changelog|bbcode}
				</div>
			</div>
		</div>
	{/if}
</div>