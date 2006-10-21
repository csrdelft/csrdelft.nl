<table class="profiel">
	<tr>
		<td rowspan="4" class="foto">{$profhtml.foto}</td>
		<th>Identiteit</th>
		<th>Adres</th>
		<th>Email/Telefoon</th>
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
			{$profhtml.land}
		</td>
		<td>
			E-mail: {$profhtml.email}<br />
			Telefoon: {$profhtml.telefoon}<br />
			Pauper: {$profhtml.mobiel}
		</td>
	</tr>
	<tr>
		<th>Studie/Lidmaatschap</th>
		<th>{if $isOudlid==true}Functie/beroep{else}Ouders{/if}</th>
		<th>Overig</th>
	</tr>
	<tr>
		<td>
			Studie: {$profhtml.studie}<br />
			Studie sinds: {$profhtml.studiejaar}<br />
			Lid sinds: {$profhtml.lidjaar}<br />
			Geboortedatum: {$profhtml.gebdatum|date_format:"%d-%m-%Y"}<br />
			{if $isOudlid!==true}
				Kring: {$profhtml.moot}.{$profhtml.kring}<br />
				{$profhtml.commissies}
			{/if}
		</td>
		<td>
			{if $isOudlid!==true}
				{$profhtml.o_adres}<br />
				{$profhtml.o_postcode} {$profhtml.o_woonplaats}<br />
				{$profhtml.o_land}<br />
				{$profhtml.o_telefoon}
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
			{$profhtml.saldi}
		</td>
	</tr>
</table>
