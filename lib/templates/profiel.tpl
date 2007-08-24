<table class="profiel">
	<tr>
		<td rowspan="4" class="foto">{$profhtml.foto}</td>
		<th>Identiteit</th>
		<th>Adres</th>
		<th>Contact</th>
	</tr>
	<tr>
		<td>
			Naam: {$profhtml.fullname}<br />
			Lid-nummer: {$profhtml.uid}<br />
			Bijnaam: {$profhtml.nickname}
		</td>
		<td>
			{$profhtml.woonoord}
			{$profhtml.adres}<br />
			{$profhtml.postcode} {$profhtml.woonplaats}<br />
			{$profhtml.land}<br />
			<a href="http://maps.google.nl/maps?daddr={$profhtml.adres|urlencode}+{$profhtml.woonplaats|urlencode}+{$profhtml.land|urlencode}">kaart</a>
		</td>
		<td>
			E-mail: {$profhtml.email}<br />
			Telefoon: {$profhtml.telefoon}<br />
			Pauper: {$profhtml.mobiel}
		</td>
	</tr>
	<tr>
		<th>Studie/Lidmaatschap</th>
		<th>{if $isOudlid==true}Functie/beroep{else}Adres ouders{/if}</th>
		<th>Overig</th>
	</tr>
	<tr>
		<td>
			Studie: {$profhtml.studie}<br />
			Studie sinds: {$profhtml.studiejaar}<br />
			Lid sinds: {$profhtml.lidjaar}<br />
			Geboortedatum: 
			{if $profhtml.gebdatum!='0000-00-00'}{$profhtml.gebdatum|date_format:"%d-%m-%Y"}{/if}
			<br />
			{if $isOudlid!==true}
				Kring: <a href="/groepen/moten.php">{$profhtml.moot}.{$profhtml.kring}</a><br />
				{$profhtml.commissies}
			{/if}
		</td>
		<td>
			{if $isOudlid!==true}
				{$profhtml.o_adres}<br />
				{$profhtml.o_postcode} {$profhtml.o_woonplaats}<br />
				{$profhtml.o_land}<br />
				{$profhtml.o_telefoon}<br />
				<a href="http://maps.google.nl/maps?daddr={$profhtml.o_adres|urlencode}+{$profhtml.o_woonplaats|urlencode}+{$profhtml.o_land|urlencode}">kaart</a>
			{else}
				{$profhtml.beroep}
			{/if}
		</td>
		<td>
			ICQ: {$profhtml.icq}<br />
			MSN: {$profhtml.msn}<br />
			Jabber: {$profhtml.jid}<br />
			Skype: {$profhtml.skype}<br />
			Website: <a href="{$profhtml.website}" class="linkExt">{$profhtml.website_kort}</a><br />
			Eetwens: {$profhtml.eetwens}<br />
			{* Bankrekening: {$profhtml.bankrekening}<br /> *}
			{$profhtml.saldi}
		</td>
	</tr>
</table>
