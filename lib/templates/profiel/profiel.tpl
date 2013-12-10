<div id="profiel" {if $profiel->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">
				{$profiel->getUid()|pasfoto}<br />
				<div class="knopjes">
					{if $profiel->magBewerken()}
						<a href="/communicatie/profiel/{$profiel->getUid()}/bewerken" class="knop" title="Bewerk dit profiel">{icon get="bewerken"}</a>
						<a href="/communicatie/profiel/{$profiel->getUid()}/voorkeuren" class="knop" title="Pas voorkeuren voor commissies aan">{icon get="report_edit"}</a>
					{/if}
					{if $isLidMod}
						<a href="/communicatie/profiel/{$profiel->getUid()}/wijzigstatus" class="knop" title="Wijzig de lidstatus">{icon get="group_edit"}</a>
					{/if}
					{if $isBestuur}
							<a href="/communicatie/profiel/{$profiel->getUid()}/dd" class="knop" title="Wijzig de lidstatus">{icon get="group_edit"}</a>
					{/if}
					{if $isAdmin}
						<a href="/tools/stats.php?uid={$profiel->getUid()}" class="knop" title="Toon bezoeklog">{icon get="server_chart"}</a>
						<a href="/communicatie/profiel/{$profiel->getUid()}/wachtwoord" class="knop"
							title="Reset wachtwoord voor {$profiel->getNaam()}"
							onclick="return confirm('Weet u zeker dat u het wachtwoord van deze gebruiker wilt resetten?')">
							{icon get="resetpassword"}</a>
						{if $loginlid->maySuTo($profiel->getLid())}
							<a href="/su/{$profiel->getUid()}/" class="knop" title="Su naar dit lid">{icon get='su'}</a>
						{/if}
					{/if}
					{if $profiel->getStatus()=='S_NOVIET' AND $loginlid->hasPermission('groep:novcie')}
						<a href="/communicatie/profiel/{$profiel->getUid()}/novietBewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk dit profiel" />Noviet bewerken</a><br />
					{/if}
					<a href="/communicatie/profiel/{$profiel->getUid()}/addToGoogleContacts/" class="knop" title="{*if $profiel->isInGoogleContacts()}Er bestaat al een contact met deze naam in je Google-contacts. Klik om te updaten.{else*}Voeg dit profiel toe aan mijn google adresboek{*/if*}"><img src="http://code.google.com/favicon.ico" /></a>
					<br />
				</div>
			</div>
			{if $melding!=''}{$melding}<br />{/if}
			<h1 title="Lid-status: {$profiel->getStatus()->getDescription()}">
				<div class="status">{if !$profiel->isLid()}{$profiel->getStatus()->getChar()}{/if}&nbsp;</div>
				{$profiel->getNaam('full', 'plain')}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div> {$profiel->getNaamLink('civitas', 'html')}<br />
			<div class="label">Lidnummer:</div> {$profhtml.uid}<br />
			{if $profhtml.nickname!=''}<div class="label">Bijnaam:</div> {$profhtml.nickname}<br />{/if}
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
						<a href="http://maps.google.nl/maps?q={$profhtml.adres|urlencode}+{$profhtml.woonplaats|urlencode}+{$profhtml.land|urlencode} ({if $profhtml.woonoord!=''}{$profiel->getWoonoord()->getNaam()}{else}{$profiel->getNaamLink('civitas', 'html')}{/if})">
							<img src="{$csr_pics}layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
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
						<a href="http://maps.google.nl/maps?q={$profhtml.o_adres|urlencode}+{$profhtml.o_woonplaats|urlencode}+{$profhtml.o_land|urlencode} (ouders van {$profiel->getNaamLink('civitas', 'html')})">
							<img src="{$csr_pics}layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
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
			<div style="clear: left;"></div>
		</div>
	</div>
	{/if}
	{if count($profiel->getContactgegevens())>0}
	<div class="profielregel">
		<div class="gegevens">
			{foreach from=$profiel->getContactgegevens() key="key" item="contact"}
				{if in_array($key, array('website', 'linkedin'))}
					<div class="label">{$key|ucfirst}:</div>
					<a href="{$contact|escape:'html'}" class="linkExt">{$contact|truncate:60|escape:'htmlall'}</a>
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
						<a href="/communicatie/lijst.php?q=lichting:{$profhtml.lidjaar}&amp;status=ALL" title="Bekijk de leden van lichting {$profhtml.lidjaar}">{$profhtml.lidjaar}</a>
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
					<a href="/communicatie/lijst.php?q=verticale:{$profiel->getVerticale()}">{$profiel->getVerticale()}</a><br />
				{/if}
				{if $profhtml.moot!=0}
					<div class="label">Oude moot:</div>
					<a href="/communicatie/lijst.php?q=moot:{$profhtml.moot}">{$profhtml.moot}</a>
				{/if}
			</div>
			<div class="familie">
				{if $profiel->getPatroon() instanceof Lid OR $profiel->getKinderen()|@count > 0}
					<a class="stamboom" href="/communicatie/stamboom.php?uid={$profiel->getUid()}" title="Stamboom van {$profiel->getNaam()}">
						<img src="http://plaetjes.csrdelft.nl/knopjes/stamboom.jpg" alt="Stamboom van {$profiel->getNaam()}" />
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
			<div style="clear: left;"></div>
		</div>
	</div>
	<div class="profielregel" id="groepen" style="clear: right;">
		<div class="gegevens">
			{$profhtml.groepen->view()}
			<div style="clear: left;"></div>
		</div>
	</div>
	{if ($profiel->isLid() OR ($loginlid->hasPermission('P_LEDEN_MOD') AND ($profhtml.soccieSaldo < 0 OR $profhtml.maalcieSaldo < 0))) AND ($saldografiek!='' OR $profhtml.bankrekening!='')}
		<div class="profielregel">
			<div class="gegevens">
				{if $profhtml.bankrekening!=''}
					<div class="label">Bankrekening:</div> {$profhtml.bankrekening}
					{if $loginlid->hasPermission('P_MAAL_MOD')}
						<span style="color: gray;">({if $profhtml.machtiging=='nee'}geen {/if}machtiging getekend)</span>
					{/if}
					<br />
				{/if}

				{if $saldografiek!=''}
					<br />
					{include file='profiel/_saldografiek.tpl'}
				{/if}
			</div>
		</div>
	{/if}

	{if $loginlid->getUid()==$profhtml.uid OR $loginlid->hasPermission('P_MAAL_MOD')}
	<div class="profielregel" id="maaltijden">
		<div class="gegevens">
			<div class="label">Recent:</div>
			<div class="data">
			{if $profhtml.recenteMaaltijden}
				<table id="recenteMaaltijden">
					{foreach from=$profhtml.recenteMaaltijden item=maaltijd}
						<tr>
							<td>{$maaltijd->getBeginMoment()|date_format:"%a %d-%m-%Y %H:%i"}</td>
							<td>{$maaltijd->getTitel()}</td>
						</tr>
					{/foreach}
				</table>
			{else}
				<span style="color: gray;">U heeft zich in het recente verleden niet aangemeld voor een maaltijd.</span>
			{/if}
			</div>
			<br />
			<div class="label">Allergie/dieet:</div>
			<div class="data">{strip}
		{if $profhtml.eetwens!=''}
			{$profhtml.eetwens}
		{/if}
		{if $loginlid->getUid()==$profhtml.uid}
			&nbsp;<a href="/corveevoorkeuren" title="Bewerk voorkeuren" class="knop">{icon get="pencil"}</a>
		{/if}
			</div>{/strip}
			<br />
		{if $profhtml.abos}
			<div class="label">Abo's:</div>
			<ul class="nobullets data">
			{foreach from=$profhtml.abos item=abonnement}
				<li>{$abonnement->getMaaltijdRepetitie()->getStandaardTitel()}</li>
			{/foreach}
			</ul>
			<br />
		{/if}
			<br />
			<div class="label">Kwalificaties:</div>
			<div class="data">{foreach from=$corveekwalificaties item=kwali}{$kwali->getCorveeFunctie()->getNaam()}<span style="color: gray;"> (sinds {$kwali->getWanneerToegewezen()})</span>{/foreach}</div>
		</div>
	</div>
	{/if}
	{if $profiel->getForumPostCount()>0 OR is_array($profhtml.recenteForumberichten) OR $loginlid->getUid()==$profiel->getUid()}
	<div class="profielregel" id="forum">
		<div class="gegevens" id="forum_gegevens">
			{if $loginlid->getUid()==$profiel->getUid()}
				<div class="label">RSS-feed:</div>
				<div class="data">
					{if $profhtml.rssToken!=''}
					<a href="{$profiel->getRssLink()}">
						{icon get='feed'} Persoonlijke RSS-feed forum
					</a>
					{/if}
					<a class="knop" href="/communicatie/profiel/{$profiel->getUid()}/rssToken#forum">Nieuwe aanvragen</a>
				</div>
				<br />
			{/if}
			{if $profiel->getForumPostCount()>0}
				<div class="label"># bijdragen:</div>
				<div class="data">
					{$profiel->getForumPostCount()} bericht{if $profiel->getForumPostCount()>1}en{/if}.
				</div>
				<div class="label">Recent:</div>
				<div class="data">
					<table id="recenteForumberichten">
						{foreach from=$profiel->getRecenteForumberichten() item=bericht}
							<tr>
								<td><a href="/communicatie/forum/reactie/{$bericht.postID}">{$bericht.titel|truncate:75|escape:'html'}</a></td>
								<td>{$bericht.datum|reldate}</td>
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
	{if $boeken OR $loginlid->getUid()==$profhtml.uid OR $gerecenseerdeboeken}
		<div class="profielregel boeken" id="boeken">
			<div class="gegevens">
				{if $boeken}
					<div class="label">Boeken:</div>
					<ul class="nobullets data">
						{foreach from=$boeken item=boek}
							<li>
								<a href="/communicatie/bibliotheek/boek/{$boek.id}" title="Boek: {$boek.titel|escape:'html'}">
									<span title="{$boek.status} boek" class="boekindicator {$boek.status}">•</span><span class="titel">{$boek.titel|escape:'html'}</span><span class="auteur">{$boek.auteur|escape:'html'}</span>
								</a>
							</li>
						{foreachelse}
							<li>Geen boeken</li>
						{/foreach}
					</ul>
				{/if}
				{if $loginlid->getUid()==$profhtml.uid}
					<a class="knop" href="/communicatie/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
					<br />
				{/if}
				{if $gerecenseerdeboeken}
					<br />
					<div class="label">Boekrecensies:</div>
					<ul class="nobullets data">
						{foreach from=$gerecenseerdeboeken item=boek}
							<li>
								<a href="/communicatie/bibliotheek/boek/{$boek.id}" title="Boek: {$boek.titel|escape:'html'}">
									<span title="{$boek.status} boek" class="boekindicator {$boek.status}">•</span><span class="titel">{$boek.titel|escape:'html'}</span><span class="auteur">{$boek.auteur|escape:'html'}</span>
								</a> {$boek.beschrijving|ubb|strip_tags}
							</li>
						{foreachelse}
							<li>Geen boeken</li>
						{/foreach}
					</ul>
				{/if}
			</div>
		</div>
	{/if}
	{if $loginlid->hasPermission('P_ADMIN,P_BESTUUR,groep:novcie') AND $profiel->getStatus()=='S_NOVIET' AND $profhtml.kgb!=''}
		<div class="profielregel" id="novcieopmerking">
			<div class="handje" onclick="toggleDiv('novcie_gegevens')">NovCie-Opmerking &raquo;</div>
			<div class="gegevens verborgen" id="novcie_gegevens">{$profhtml.kgb|ubb}</div>
		</div>
	{/if}
	{if ($isAdmin OR $isLidMod) AND $profhtml.changelog!=''}
		<div class="profielregel" id="changelog">
			<div class="gegevens">
				<div class="handje" onclick="toggleDiv('changelog_gegevens'); this.remove()">Bewerklog &raquo;</div>
				<div class="verborgen" id="changelog_gegevens">
					{$profhtml.changelog|ubb}
				</div>
			</div>
		</div>
	{/if}
</div>
