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
	</style>
	{/literal}
</head>
<body>

<h1>C.S.R.-maaltijd {$maaltijd.datum|date_format:$datumFormaat}</h1>
<p>
Regels omtrent het betalen van de maaltijden op Confide:
</p>
<ul>
	<li>maaltijdprijs: &euro; 2,50</li>
	<li>niet betaald = nb</li>
	<li>2,50 betaald = kruisje (x)</li>
	<li>ander bedrag ingelegd: schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
	<li>bevat derde kolom 'ok'? Dan hebt u nog voldoende tegoed voor deze maaltijd</li>
	<li>als je géén tegoed hebt bij de maalcie betekent een niet direct betaalde maaltijd 20 cent boete!</li>
</ul>
<p>
Tafelpraeses is vandaag {$maaltijd.tafelpraeses}
</p>
{if !$maaltijd.gesloten}
	<h1 style="color: red">De inschrijving voor deze maaltijd is nog niet gesloten</h1>
<p>
	<a href="/maaltijden/lijst/{$maaltijd.id}/sluiten" onclick="return confirm('Weet u zeker dat u deze maaltijd wil sluiten?')">
		Nu sluiten! (N.B. Dit is een onomkeerbare stap!!)
	</a>
</p>
{/if}
{if $maaltijd.aantal>0}
	{assign var=nummer value=1}
	{table_foreach from=$maaltijd.aanmeldingen inner=rows item=aanmelding table_attr='class="inschrijvingen"' cols=2 name=aanmeldingen}
		{$nummer++}</td>
		<td>{$aanmelding.naam}
		{if $aanmelding.eetwens!=''}<br /><strong>{$aanmelding.eetwens}</strong>{/if}
		{if $aanmelding.gasten_opmerking!=''}<br /><strong>Gasten opmerking: {$aanmelding.gasten_opmerking}</strong>{/if}
		</td>
		<td style="width: 20px; text-align: right;">
			{if $aanmelding.saldo!=''}
				{if $aanmelding.saldo>=2.50}ok{elseif $aanmelding.saldo>=0}&lt;2,5{else}&lt;0{/if}
			{/if}
		</td><td style="width: 40px;">&nbsp;
	{/table_foreach}
{else}
	Nog geen aanmeldingen voor deze maaltijd.
{/if}
<table class="overzicht">
	<tr>
		<td class="overzicht">
			<strong>Overzicht</strong><br />
			<table style="width: 100%">
				<tr><td>Aantal inschrijvingen</td><td>{$maaltijd.aantal}</td></tr>
				<tr><td>Marge i.v.m. gasten</td><td>{$maaltijd.marge}</td></tr>
				<tr><td>Eters</td><td>{$maaltijd.totaal}</td></tr>
				<tr><td>Budget koks</td><td>&euro; {$maaltijd.budget|string_format:"%.2f"}</td></tr>
			</table>	
		</td>
		<td class="corvee">
			<strong>Corvee</strong><br />
			<table  style="width: 100%">
				<tr><td>koks:</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
				<tr><td>afwassers:</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
			</table>
		</td></tr>
</table>
</body>
</html>
