<div id="profiel" {if $lid->isJarig()}class="jarig"{/if}>
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">
				{$profhtml.uid|pasfoto}<br />
				<div class="knopjes">
					{if $magBewerken}
						<a href="/communicatie/profiel/{$profhtml.uid}/bewerken" class="knop" title="Bewerk dit profiel">{icon get="bewerken"}</a>
					{/if}

					{if $isAdmin}
						<a href="/tools/stats.php?uid={$profhtml.uid}" class="knop" title="Toon bezoeklog">{icon get="server_chart"}</a>
						<a href="/communicatie/profiel/{$profhtml.uid}/wachtwoord" class="knop" title="Reset wachtwoord voor {$lid->getNaam()}" onclick="return confirm('Weet u zeker dat u het wachtwoord van deze gebruiker wilt resetten?')">
							{icon get="resetpassword"}</a>
						{if $loginlid->maySuTo($lid)}
							<a href="/su/{$profhtml.uid}/" class="knop" title="Su naar dit lid">{icon get='su'}</a>
						{/if}
					{/if}
					{if $lid->getStatus()=='S_NOVIET' AND $loginlid->hasPermission('groep:novcie')}
						<a href="/communicatie/profiel/{$profhtml.uid}/novietBewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk dit profiel" />Noviet bewerken</a><br />
					{/if}
					<a href="/communicatie/profiel/{$profhtml.uid}/addToGoogleContacts/" class="knop{*if $lid->isInGoogleContacts()} inGoogleContacts{/if*}" title="{*if $lid->isInGoogleContacts()}Er bestaat al een contact met deze naam in je Google-contacts. Klik om te updaten.{else*}Voeg dit profiel toe aan mijn google adresboek{*/if*}"><img src="http://code.google.com/favicon.ico" /></a>
					<br />
				</div>
			</div>
			{if $melding!=''}{$melding}<br />{/if}
			<h1 title="Lid-status: {$lid->getStatusDescription()}">
				<div class="status">{if !$lid->isLid()}{$lid->getStatusChar()}{/if}&nbsp;</div>
				{$profhtml.uid|csrnaam:'full':'plain'}
			</h1>
		</div>
	</div>

	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div> {$lid->getNaamLink('civitas', 'html')}<br />
			<div class="label">Lidnummer:</div> {$profhtml.uid}<br />
			<div class="label">Bijnaam:</div> {$profhtml.nickname}<br />
			{if $profhtml.voorletters!=''}<div class="label">Voorletters:</div> {$profhtml.voorletters}<br />{/if}
			{if $profhtml.gebdatum!='0000-00-00'}<div class="label">Geb.datum:</div> {$profhtml.gebdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if $lid->getStatus()=='S_OVERLEDEN' AND $profhtml.sterfdatum!='0000-00-00'}<div class="label">Overleden op:</div> {$profhtml.sterfdatum|date_format:"%d-%m-%Y"}<br />{/if}
			{if $lid->getEchtgenoot() instanceof Lid}
					<div class="label">{if $lid->getEchtgenoot()->getGeslacht()=='v'}Echtgenote{else}Echtgenoot{/if}:</div>
					{$lid->getEchtgenoot()->getNaamLink('civitas', 'link')}<br />
			{/if}
		</div>
	</div>
	{if $lid->getStatus()!='S_OVERLEDEN' AND ($profhtml.adres!='' OR $profhtml.o_adres!='')}
	<div class="profielregel">
		<div class="gegevens">
			<div class="gegevenszelf">
				<div class="label">
					{if $profhtml.adres!=''}
						<a href="http://maps.google.nl/maps?q={$profhtml.adres|urlencode}+{$profhtml.woonplaats|urlencode}+{$profhtml.land|urlencode} ({if $profhtml.woonoord!=''}{$lid->getWoonoord()->getNaam()}{else}{$lid->getNaamLink('civitas', 'html')}{/if})">
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
			{if $lid->isLid()}
			<div class="gegevensouders">
				{if $profhtml.o_adres!=''}
					<div class="label">
						<a href="http://maps.google.nl/maps?q={$profhtml.o_adres|urlencode}+{$profhtml.o_woonplaats|urlencode}+{$profhtml.o_land|urlencode} (ouders van {$lid->getNaamLink('civitas', 'html')})">
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
	{if $profhtml.email!='' OR $profhtml.icq!='' OR $profhtml.msn!='' OR $profhtml.jid!='' OR $profhtml.skype!='' OR $profhtml.linkedin!='' OR $profhtml.website!=''}
	<div class="profielregel">
		<div class="gegevens">
			{if $profhtml.email!=''}<div class="label">Email:</div><a href="mailto:{$profhtml.email}">{$profhtml.email}</a><br />{/if}
			{if $profhtml.icq!=''}<div class="label">ICQ:</div> {$profhtml.icq}<br />{/if}
			{if $profhtml.msn!=''}<div class="label">MSN:</div> {$profhtml.msn}<br />{/if}
			{if $profhtml.jid!=''}<div class="label">Jabber/GTalk:</div> {$profhtml.jid}<br />{/if}
			{if $profhtml.skype!=''}<div class="label">Skype:</div> {$profhtml.skype}<br />{/if}
			{if $profhtml.linkedin!=''}<div class="label">LinkedIn:</div> <a href="{$profhtml.linkedin}" class="linkExt">{$profhtml.linkedin|truncate:50}</a><br />{/if}
			{if $profhtml.website!=''}<div class="label">Website:</div> <a href="{$profhtml.website}" class="linkExt">{$profhtml.website|truncate:30}</a><br />{/if}
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
					{if $isOudlid AND $profhtml.lidafdatum!='0000-00-00'} tot {$profhtml.lidafdatum|substr:0:4}{/if}<br />
				<div class="label">Status:</div> {$lid->getStatusDescription()}<br />
				<br />

				{if $isOudlid}
					{if $profhtml.beroep!=''}<div class="label">Beroep/werk:</div><div class="data">{$profhtml.beroep}</div><br />{/if}
				{else}
					{if $profhtml.kring!=0}
						<div class="label">Kring:</div>
						{$lid->getKring(true)}
						<br />
					{/if}
				{/if}
				{if $profhtml.moot!=0}
					<div class="label">Oude moot:</div>
					<a href="/communicatie/lijst.php?q=moot:{$profhtml.moot}">{$profhtml.moot}</a>
				{/if}
			</div>
			<div class="familie">
				{if $lid->getPatroon() instanceof Lid OR $lid->getKinderen()|@count > 0}
					<a class="stamboom" href="/communicatie/stamboom.php?uid={$lid->getUid()}" title="Stamboom van {$lid->getNaam()}">
						<img src="http://plaetjes.csrdelft.nl/knopjes/stamboom.jpg" alt="Stamboom van {$lid->getNaam()}" />
					</a>
				{/if}
				{if $lid->getPatroon() instanceof Lid}
					<div class="label">{if $lid->getPatroon()->getGeslacht()=='v'}M{else}P{/if}atroon:</div>
					{$lid->getPatroon()->getNaamLink('civitas', 'link')}<br />
				{/if}
				{if $lid->getKinderen()|@count > 0}
					<div class="label">Kinderen:</div>
					<div class="data">
						{foreach from=$lid->getKinderen() item=kind name=kinderen}
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
	{if $lid->isLid() AND ($saldografiek!='' OR $profhtml.bankrekening!='')}
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

	{if $loginlid->getUid()==$profhtml.uid OR $profhtml.eetwens!='' OR is_array($profhtml.recenteMaaltijden)}
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
					<ul class="nobullets data">
						{foreach from=$profhtml.recenteMaaltijden item=maaltijd}
							<li><em title="{$maaltijd.datum|date_format:"%Y-%m-%d"}">{$maaltijd.datum|date_format:"%a %d-%m"}</em> - {$maaltijd.tekst|escape:'html'}</li>
						{/foreach}
					</ul>
				{/if}
			{/if}
		</div>
	</div>
	{/if}
	{if is_array($profhtml.recenteForumberichten) OR $loginlid->getUid()==$lid->getUid()}
	<div class="profielregel" id="forum">
		<div class="gegevens" id="forum_gegevens">
			{if $loginlid->getUid()==$lid->getUid()}
				<div class="label">RSS-feed:</div>
				<div class="data">
					{if $profhtml.rssToken!=''}
					<a href="http://csrdelft.nl/communicatie/forum/rss/{$profhtml.rssToken}.xml">
						{icon get='feed'} Persoonlijke RSS-feed forum
					</a>
					{/if}
					<a class="knop" href="/communicatie/profiel/{$lid->getUid()}/rssToken#forum">Nieuwe aanvragen</a>
				</div>
			<br />
			{/if}

			{if $profhtml.berichtCount>0}
				<div class="label"># bijdragen:</div>
				<div class="data">
					{$profhtml.berichtCount} berichten.
				</div>
			{/if}
			{if is_array($profhtml.recenteForumberichten)}
				<div class="label">Recent:</div>
				<div class="data">
					<table id="recenteForumberichten">
						{foreach from=$profhtml.recenteForumberichten item=bericht}
							<tr>
								<td><a href="/communicatie/forum/reactie/{$bericht.postID}">{$bericht.titel|truncate:75|escape:'html'}</a></td>
								<td>{$bericht.datum|reldate}</td>
							</tr>
						{/foreach}
					</table>
				</div>
			{/if}
		</div>
	</div>
	{/if}
	{if $loginlid->hasPermission('P_ADMIN,P_BESTUUR,groep:novcie') AND $lid->getStatus()=='S_NOVIET' AND $profhtml.kgb!=''}
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
