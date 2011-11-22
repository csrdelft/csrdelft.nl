<div id="profiel" {if $profiel->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">
				{$profiel->getUid()|pasfoto}<br />
				<div class="knopjes">
					{if $profiel->magBewerken()}
						<a href="/communicatie/profiel/{$profiel->getUid()}/bewerken" class="knop" title="Bewerk dit profiel">{icon get="bewerken"}</a>
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
			<h1 title="Lid-status: {$profiel->getStatusDescription()}">
				<div class="status">{if !$profiel->isLid()}{$profiel->getStatusChar()}{/if}&nbsp;</div>
				{$profiel->getNaam('full', 'plain')}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div> {$profiel->getNaamLink('civitas', 'html')}<br />
			<div class="label">Lidnummer:</div> {$profhtml.uid}<br />
			<div class="label">Bijnaam:</div> {$profhtml.nickname}<br />
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
					{if $profiel->isOudlid() AND $profhtml.lidafdatum!='0000-00-00'} tot {$profhtml.lidafdatum|substr:0:4}{/if}<br />
				<div class="label">Status:</div> {$profiel->getStatusDescription()}<br />
				<br />

				{if $profiel->isOudlid()}
					{if $profhtml.beroep!=''}<div class="label">Beroep/werk:</div><div class="data">{$profhtml.beroep}</div><br />{/if}
				{else}
					{if $profhtml.kring!=0}
						<div class="label">Kring:</div>
						{$profiel->getKring(true)}
						<br />
					{/if}
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
					{$profiel->getPatroon()->getNaamLink('civitas', 'link')}<br />
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
					{include file='profiel/_saldografiek.tpl'}
				{/if}
			</div>
		</div>
	{/if}

	{if $loginlid->getUid()==$profhtml.uid OR $profhtml.eetwens!='' OR is_array($profhtml.recenteMaaltijden) OR $corveetaken!==null}
	<div class="profielregel" id="maaltijden">
		<div class="gegevens">
			{if $profhtml.eetwens!=''}
				<div class="label">Dieet:</div>
				<div class="data">{$profhtml.eetwens}{if $loginlid->getUid()==$profhtml.uid}&nbsp;
				 <a href="/actueel/maaltijden/voorkeuren/" class="knop" ><img src="{$csr_pics}forum/bewerken.png" title="Maaltijdvoorkeuren bewerken" /></a>
			{/if}</div>
				<br />
			{/if}
			{if $profhtml.abos|@count > 0}
				<div class="label">Abo's:</div>
				<ul class="nobullets data">
				{foreach from=$profhtml.abos item=abo}
					<li>{$abo}</li>
				{/foreach}
				</ul>
				<br />
			{/if}
			{if $loginlid->getUid()==$profhtml.uid OR $loginlid->hasPermission('P_MAAL_MOD')}
				{if is_array($profhtml.recenteMaaltijden)}
					<div class="label">Recent:</div>
					<div class="data">
						<table id="recenteMaaltijden">
							{foreach from=$profhtml.recenteMaaltijden item=maaltijd}
								<tr>
									<td><span title="{$maaltijd.datum|date_format:"%Y-%m-%d"}">{$maaltijd.datum|date_format:"%a"}</span></td>
									<td><span title="{$maaltijd.datum|date_format:"%Y-%m-%d"}">{$maaltijd.datum|date_format:"%d-%m"}</span></td>
									<td> - {$maaltijd.tekst|truncate:50|escape:'html'}</td>
								</tr>
							{foreachelse}
								<tr><td>Geen maaltijden</td></tr>
							{/foreach}
						</table>
					</div><br />
				{/if}
			{/if}
			{if $profiel->isLid() AND $corveetaken.aantal!==null}
				<div class="label" title="Punten van vorig jaar + punten uit taken + bonuspunten">Corveepunten:</div>
				<div class="data" title="Punten van vorig jaar + punten uit taken + bonuspunten">{$corveetaken.lid.corvee_punten_totaal} punten</div>
				<div class="label">Bonuspunten:</div>
				<div class="data">{$corveetaken.lid.corvee_punten_bonus} punten</div>
				<div class="label">Vrijstelling:</div>
				<div class="data">{$corveetaken.lid.corvee_vrijstelling} %</div>
				<br />
			{/if}
			{if $profiel->isLid()}
				<div class="half">
					<div class="label">Voorkeuren:</div>
					<ul class="nobullets data" title="Ik kom graag deze taken doen">
						{if $corveevoorkeuren.ma_kok}	<li>Maandag koken</li>{/if}
						{if $corveevoorkeuren.ma_afwas}	<li>Maandag afwassen</li>{/if}
						{if $corveevoorkeuren.do_kok}	<li>Donderdag koken</li>{/if}
						{if $corveevoorkeuren.do_afwas}	<li>Donderdag afwassen</li>{/if}
						{if $corveevoorkeuren.theedoek}	<li>Theedoeken wassen</li>{/if}
						{if $corveevoorkeuren.afzuigkap}<li>Afzuigkap schoonmaken</li>{/if}
						{if $corveevoorkeuren.frituur}	<li>Frituur schoonmaken</li>{/if}
						{if $corveevoorkeuren.keuken}	<li>Keuken schoonmaken</li>{/if}
					</ul>
				</div>
				<div>
					{if $loginlid->getUid()==$corveetaken.lid.uid}
						<a href="/actueel/maaltijden/voorkeuren/" class="knop" >
							<img src="{$csr_pics}forum/bewerken.png" title="Corveevoorkeuren bewerken" />
						</a>
					{/if}
				</div><div style="clear: left;"></div>
				<br />
			{/if}
			{if $profiel->isLid() AND $corveetaken.aantal!==null}
				<div class="label">Taken:</div>
				<div class="data">
					<table id="corveeTaken">
						{foreach from=$corveetaken.taken item=taak}
							<tr{if $taak.datum<$startpuntentelling} class="old"{/if}>
								{*datum, taak,
								maalid, tekst, type, punten_toegekend, type,  *}
								<td><span title="{$taak.datum|date_format:"%Y-%m-%d"}">{$taak.datum|date_format:"%a"}</span></td>
								<td><span title="{$taak.datum|date_format:"%Y-%m-%d"}">{$taak.datum|date_format:"%d-%m"}</span></td>
								<td>{$taak.taak}</td>
								<td title="{$taak.tekst|escape:'html'}">{$taak.tekst|truncate:20|escape:'html'}</td>
								<td>{$taak.punten} </td>
								<td>punten {if $taak.punten_toegekend=='onbekend'}({if $loginlid->hasPermission('P_MAAL_MOD')}<a href="/actueel/maaltijden/corveebeheer/puntenbewerk/{$taak.maalid}#corveepuntenFormulier">niet toegekend</a>{else}niet toegekend{/if}){/if}</td>
							</tr>
						{foreachelse}
							<tr><td>Geen corveetaken</td></tr>
						{/foreach}
					</table>
				</div>
			{/if}
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
	
	<div class="profielregel" id="boeken">
		<div class="gegevens">
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
		</div>
	</div>
	
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
