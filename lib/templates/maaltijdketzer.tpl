<h1>Maaltijden</h1>
<p>
Op deze pagina kunt u zich inschrijven voor maaltijden op Confide. Onderstaande tabel toont de maaltijden in de
komende weken. Onder "Kom ik eten?" ziet u de huidige status van uw inschrijving voor de maaltijd.<br />
<br />
U kunt uw inschrijving wijzigen door gebruik te maken van de opties die aan het einde van elke regel staan.<br />
N.B. De maaltijdinschrijving sluit op de dag van de maaltijd rond 15:00, als de koks de lijst met aanmeldingen
uitprinten. Vanaf dat moment zal deze ketzer u niet meer willen aan- of afmelden!<br />
<br />
Prefereert u vegetarisch eten, of heeft u speciale eetgewoontes of een dieet, gebruik dan het vakje 'Eetwens' in uw
<a href="/intern/profiel.php">profielinstellingen</a> om dat aan te geven.<br />
{if $maal.zelf.error!=''}
	<span class="waarschuwing">N.B.: {$maal.zelf.error|escape:'htmlall'}</span>
{/if}
{if $maal.zelf.maaltijden|@count==0}
	&#8226; Helaas, er is binnenkort geen maaltijd op Confide.<br /><br />
{else}
	<table style="width: 100%;">
		<tr>
			<th>Maaltijd begint om:</th>
			<th>Menu</th>
			<th>Aantal(Max)</th>
			<th>Kom ik eten?</th>
			<th>Wijzig in:</th>
		</tr>
		{foreach from=$maal.zelf.maaltijden item=maaltijd}
			<tr>
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>
					{if $toonLijsten}<a href="/maaltijden/maaltijdlijst.php?maalid={$maaltijd.id}">{/if}
						{$maaltijd.tekst|escape:'html'}
					{if $toonLijsten}</a>{/if}				
				</td>
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
						<a href="{$smarty.server.PHP_SELF}?a=aan&amp;m={$maaltijd.id}">[ ik kom WEL! ]</a>
					{elseif $maaltijd.actie=='af'}
						<a href="{$smarty.server.PHP_SELF}?a=af&amp;m={$maaltijd.id}">[ ik kom NIET! ]</a>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
</p>
<br />
<h2>Maaltijdabonnementen</h2>
<p>
	<table style="width: 100%;">
		<tr>
			<td>
				{if $maal.abo.abos|@count==0}
					Er is geen maaltijdabonnement geactiveerd.
				{else}
					<table style="width: 300px;">
						{foreach from=$maal.abo.abos key=abosoort item=abotekst}
							<tr>
								<td>&#8226; {$abotekst}</td>
								<td>
									<a href="{$smarty.server.PHP_SELF}?a=delabo&abo={$abosoort}">[ uitschakelen ]</a>
								</td>
							</tr>
						{/foreach}
					</table>
				{/if}
			</td>
			<td>
				{if $maal.abo.nietAbos|@count!=0}
					<form action="{$smarty.server.PHP_SELF}" method="POST">
						<input type="hidden" name="a" value="addabo" />
						<label for="addabo_abo">Voeg een abonnement toe:</label>
						<select name="abo" id="addabo_abo">
							{foreach from=$maal.abo.nietAbos key=abosoort item=abotekst}
								<option value="{$abosoort}">{$abotekst}</option>
							{/foreach}
						</select>
						<input type="submit" name="fuh" value="toevoegen" />
					</form>
				{/if}
			</td>
		</tr>
	</table>
</p>
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
				<th>U heeft anderen voor deze maaltijd aangemeld:</h>
			</tr>
			{foreach from=$maal.anderen.maaltijden item=maaltijd}
			<form action="{$smarty.server.PHP_SELF}" method="POST">
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
										<a href="{$smarty.server.PHP_SELF}?a=af&m={$maaltijd.id}&uid={$uid}">[ afmelden ]</a>
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
	U kunt op uw naam gasten aanmelden voor de maaltijd.<br />
	Dit onderdeel is nog niet afgerond helaas.<br />
	Gasten kunt u opgeven door even te bellen naar het bestuur.<br />
</p>
