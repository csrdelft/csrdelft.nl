<h1>Maaltijden</h1>
<p>
Op deze pagina kunt u zich inschrijven voor maaltijden op Confide. Onderstaande tabel toont de maaltijden in de
komende weken. Onder "Kom ik eten?" ziet u de huidige status van uw inschrijving voor de maaltijd.<br />
<br />
N.B. De maaltijdinschrijving sluit op de dag van de maaltijd rond <strong>15:00</strong>, als de koks de lijst met aanmeldingen
uitprinten. Vanaf dat moment zal deze ketzer u niet meer willen aan- of afmelden!<br />
</p>
{if $maal.zelf.error!=''}<span class="waarschuwing">N.B.: {$maal.zelf.error|escape:'htmlall'}</span>{/if}
{if $maal.zelf.maaltijden|@count==0}
	<p>&#8226; Helaas, er is binnenkort geen maaltijd op Confide.</p>
{else}
	<table class="maaltijden">
		<tr>
			{if $toonLijsten}<th>&nbsp;</th>{/if}
			<th>Maaltijd begint om:</th>
			<th>Omschrijving</th>
			<th>Aantal(Max)</th>
			<th>Kom ik eten?</th>
			<th>Actie</th>
		</tr>
		{foreach from=$maal.zelf.maaltijden item=maaltijd}
			<tr>
				{if $toonLijsten}
					<td><a href="/maaltijden/lijst/{$maaltijd.id}" class="knop">lijst printen</a></td>
				{/if}
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>{$maaltijd.tekst|escape:'html'}</td>
				<td>
					{if $maaltijd.aantal < $maaltijd.max}
						{$maaltijd.aantal} ({$maaltijd.max})
					{else}
						VOL ({$maaltijd.max})
					{/if}
				</td>
				<td>
					<strong>
						{if $maaltijd.status=='AAN'}
							<span style="color: green;">JA!</span>
						{elseif $maaltijd.status=='ABO'}
							<span style="color: green;">JA! (Abo)</span>
						{elseif $maaltijd.status=='AF'}
							<span style="color: red;">NEE</span>
						{else}
							NEE
						{/if}
					</strong>
				</td>
				<td>
					{if $maaltijd.gesloten==1}
						Inschrijving Gesloten
					{elseif $maaltijd.actie=='aan'}
						<a href="{$smarty.server.PHP_SELF}?a=aan&amp;m={$maaltijd.id}"><strong>aan</strong>melden</a>
					{elseif $maaltijd.actie=='af'}
						<a href="{$smarty.server.PHP_SELF}?a=af&amp;m={$maaltijd.id}"><strong>af</strong>melden</a>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
<br />
<h2>Andere verenigingsleden aanmelden</h2>
<p>
	Het is voor leden alleen mogelijk andere leden aan te melden binnen 48 uur voordat de maaltijd plaatsvindt.
	U kunt iemand aanmelden met zijn/haar 4-cijferige lid-nummer. Als iemand u vraagt hem/haar in te schrijven,
	vraag hier dan even naar, of zoek het op in de ledenlijst.<br />
	<br />
	{if $maal.anderen.error!=''}
		<span class="waarschuwing">N.B.: {$maal.anderen.error|escape:'html'}</span>
	{/if}
	{if $maal.anderen.maaltijden|@count==0}
		&#8226; Helaas, er is binnenkort geen maaltijd op Confide.<br />
	{else}
		<table style="width: 100%;">
			<tr>
				<th>Maaltijd:</th>
				<th>Lid-nummer:</th>
				<th>&nbsp;</th>
				<th>U heeft anderen voor deze maaltijd aangemeld:</th>
			</tr>
			{foreach from=$maal.anderen.maaltijden item=maaltijd}
			<form action="{$smarty.server.PHP_SELF}" method="post">
			<input type="hidden" name="a" value="aan" />
			<input type="hidden" name="m" value="{$maaltijd.id}" />
				<tr>
					<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				{if $maaltijd.gesloten == 1}
					<td colspan="2">Inschrijving gesloten</td>
				{else}
					<td><input type="text" name="uid" style="width:140px;" /></td>
					<td><input type="submit" name="foo" value="aanmelden" /></td>
				{/if}
				<td>
					{if is_array($maaltijd.derden)}
						<table style="width: 100%;">
							{foreach from=$maaltijd.derden key=uid item=naam}
								<tr>
									<td>{$naam}</td>
									<td>
										{if $maaltijd.gesloten != 1}
											<a href="{$smarty.server.PHP_SELF}?a=af&m={$maaltijd.id}&uid={$uid}">[ afmelden ]</a>
										{/if}
									</td>
								</tr>
							{/foreach}
						</table>
					{else}
						-
					{/if}
				</td>
			</tr>
			</form>
			{/foreach}
		</table>
	{/if}	
</p>
<h2>Gasten aanmelden</h2>
<p>
	Als u staat ingeschreven voor een maaltijd, kunt u op uw naam gasten aanmelden voor de maaltijd.<br />
	Vul in het vak 'gasten' het aantal in. Het veld 'opmerking' kunt u gebruiken voor eetwensen.<br />
</p>
<table class="maaltijden">
	<tr>
		<th>Maaltijd begint om:</th>
		<th>Omschrijving</th>
		<th>Gasten</th>
		<th>Opmerking</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$maal.zelf.maaltijden item=maaltijd}
		{if $maaltijd.status=='AAN' || $maaltijd.status=='ABO'}
			<tr>
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>{$maaltijd.tekst|escape:'html'}</td>
				{if $maaltijd.gesloten == 1}
					<td>{$maaltijd.gasten|escape:'html'}</td>
					<td colspan="2">{$maaltijd.opmerking|escape:'html'}</td>
				{else}
					<form action="{$smarty.server.PHP_SELF}" method="post">
					<td><input type="hidden" name="a" value="gasten" />
					<input type="hidden" name="m" value="{$maaltijd.id}" />
					<input type="text" name="gasten" style="width:60px;" value="{$maaltijd.gasten}" /></td>
					<td><input type="text" name="opmerking" style="width:250px;" value="{$maaltijd.opmerking|escape:'html'}"/></td>
					<td><input type="submit" name="foo" value="aanpassen" /></td>						
					</form>
				{/if}
			</tr>
		{/if}
	{/foreach}
</table>