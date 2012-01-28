<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>Maaltijdaanmeldingen van {$maaltijd.datum|date_format:$datumFormaat}</title>
  {literal}
  <style type="text/css">
		body{ font-family: verdana; font-size: 10px; }
		a{ text-decoration: none; border: 0px; }
		img{ }
		table.hoofdtabel{ border: 0px; width: 100%; }
		table.inschrijvingen{
			border-collapse: collapse;
			border: 1px solid black;
			width: 100%;
		}
		table.inschrijvingen TD{
		 	border: 1px solid black;
		 	padding: 3px 5px 2px 10px;
		 	vertical-align: top;
		}
		table.inschrijvingen TD.nummer{ 			width: 20px; }
		table.inschrijvingen TD.vink-vakje{ 	width: 40px; }
		table.overzicht{
			border-collapse: collapse;
			border: 0px;
			width: 100%;
		}
		table.overzicht TD{
			width: 40%;
			vertical-align: top;
			padding: 3px 5px 2px 10px;
		}
		table.overzicht TD.overzicht{ border-right: thin solid black; } 
		table.overzicht TD.corvee{		border-left: thin solid black;  }
		img{ float: right;}
	</style>
	{/literal}
</head>
<body>
<img alt="Beeldmerk van de Vereniging" src="http://plaetjes.csrdelft.nl/layout/beeldmerk.jpg"/>
<h1>C.S.R.-maaltijd {$maaltijd.datum|date_format:$datumFormaat}</h1>
<p>
Regels omtrent het betalen van de maaltijden op Confide:
</p>
<ul>
	<li>maaltijdprijs: &euro; {$maaltijd.prijs|string_format:"%.2f"}</li>
	<li>niet betaald = nb</li>
	<li>betaald met machtiging = omcirkel 'm' en vul bedrag in </li>
	<li>contant betaald = bedrag invullen</li>
	<li>Schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
	<li>bevat derde kolom 'ok'? Dan hebt u nog voldoende tegoed voor deze maaltijd</li>
	<li>als je géén tegoed hebt bij de maalcie betekent een niet direct betaalde maaltijd een boete van 20 cent, 1 euro of 2 euro, afhankelijk van hoe negatief je saldo is!</li>
</ul>
<p>
{if $maaltijd.tafelpraeses!='Am. Lid'}Tafelpraeses is vandaag {$maaltijd.tafelpraeses}{/if}
</p>
{if !$maaltijd.gesloten}
	<h1 style="color: red">De inschrijving voor deze maaltijd is nog niet gesloten</h1>
<p>
	{if $maaltijd.magSluiten}
		<a href="/actueel/maaltijden/lijst/{$maaltijd.id}/sluiten" onclick="return confirm('Weet u zeker dat u deze maaltijd wil sluiten?')">
			Nu sluiten! (N.B. Dit is een onomkeerbare stap!!)
		</a>
	{/if}
</p>
{/if}
{if $maaltijd.aantal>0}
	{assign var=nummer value=1}
	{table_foreach from=$maaltijd.aanmeldingen inner=rows item=aanmelding table_attr='class="inschrijvingen"' cols=2 name=aanmeldingen}
		{$nummer++}</td>
		<td>{$aanmelding.naam}
			{if $aanmelding.eetwens!=''}<br 
/><strong>{$aanmelding.eetwens|wordwrap:35:"<br />":true}</strong>{/if}
			{if $aanmelding.gasten_opmerking!=''}<br /><strong>Gasten opmerking: {$aanmelding.gasten_opmerking}</strong>{/if}
		</td>
		<td style="width: 20px; text-align: right;">
			{if $aanmelding.saldo!=''}
				{if $aanmelding.saldo>$maaltijd.prijs}ok{elseif $aanmelding.saldo>($maaltijd.prijs-0.001)}{$maaltijd.prijs|string_format:"%.2f"}{elseif $aanmelding.saldo>0.001}&lt;{$maaltijd.prijs|string_format:"%.2f"}{elseif $aanmelding.saldo>-0.001}0{else}&lt;0{/if}
			{/if}
		</td><td style="width: 40px;">&nbsp
		</td><td style="width: 20px; color: #AAAAAA">m
	{/table_foreach}
{else}
	Nog geen aanmeldingen voor deze maaltijd.
{/if}
<table class="overzicht">
	<tr>
		<td class="overzicht">
			<strong>Maaltijdgegevens:</strong><br />
			<table style="width: 100%">
				<tr><td>Aantal inschrijvingen</td><td>{$maaltijd.aantal}</td></tr>
				<tr><td>Marge i.v.m. gasten</td><td>{$maaltijd.marge}</td></tr>
				<tr><td>Eters</td><td>{$maaltijd.totaal}</td></tr>
				<tr><td>Budget koks</td><td>&euro; {$maaltijd.budget|string_format:"%.2f"}</td></tr>
			</table>	
		</td>
		<td class="corvee">
			<strong>Corvee</strong><br />
				<table>
					<tr>
						<td>Koks:</td>
						<td>
						{* kwalikok *}
						{section name=kwalikoks loop=$maaltijd.kwalikoks}
							{assign var='it' value=$smarty.section.kwalikoks.iteration-1}
							{assign var='kwalikok' value=$maaltijd.taken.kwalikoks.$it}
							{if $kwalikok!=''}{$kwalikok|csrnaam}{else}...{/if} (Kwalikok)<br />
						{/section}
						{* koks *}
						{section name=koks loop=$maaltijd.koks}
							{assign var='it' value=$smarty.section.koks.iteration-1}
							{assign var='kok' value=$maaltijd.taken.koks.$it}
							{if $kok!=''}{$kok|csrnaam}{else}...{/if}<br />
						{/section}
						</td>
					</tr>
					<tr>
						<td>Afwassers:</td>
						<td>
						{* kwaliafwassers (maximaal 1) *}
						{if $maaltijd.kwaliafwassers > 0 }
							{assign var='kwaliafwas' value=$maaltijd.taken.kwaliafwassers.0}
							{if $kwaliafwas!=''}{$kwaliafwas|csrnaam}{else}...{/if} (kwaliafwasser)<br />
						{/if}
						{* afwassers *}
						{section name=afwassers loop=$maaltijd.afwassers}
							{assign var='it' value=$smarty.section.afwassers.iteration-1}
							{assign var='afwas' value=$maaltijd.taken.afwassers.$it}
							{if $afwas!=''}{$afwas|csrnaam}{else}...{/if}<br />
						{/section}
						</td>
					</tr>
					<tr>
						<td>Theedoekwassers:</td>
						<td>
						{section name=theedoeken loop=$maaltijd.theedoeken}					
							{assign var='it' value=$smarty.section.theedoeken.iteration-1}
							{assign var='theedoek' value=$maaltijd.taken.theedoeken.$it}
							{if $theedoek!=''}{$theedoek|csrnaam}{else}...{/if}<br />
						{/section}
						</td>
					</tr>
				</table>
		</td></tr>
</table>
</body>
</html>
