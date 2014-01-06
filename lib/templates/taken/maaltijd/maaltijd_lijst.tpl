<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>{strip}
<head>
	<title>{$kop} {$maaltijd->getDatum()|date_format:"%A %e %B"}</title>
	<link type="text/css" href="/layout/maaltijdlijst.css" rel="stylesheet">
	<script type="text/javascript" src="/layout/js/jquery.js"></script>
	<script type="text/javascript" src="/layout/js/taken.js"></script>
</head>
<body>
<img alt="Beeldmerk van de Vereniging" src="{$CSR_PICS}/layout/beeldmerk.jpg" style="float: right; padding: 0px 50px;" />
<h1>{$kop} op {$maaltijd->getDatum()|date_format:"%A %e %B %Y"}</h1>
<div class="header">{$GLOBALS.maaltijdlijst_tekst|replace:'MAALTIJDPRIJS':$prijs}</div>
{if !$maaltijd->getIsGesloten()}
	<h2 style="color: red">De inschrijving voor deze maaltijd is nog niet gesloten
	{if !$maaltijd->getIsVerwijderd() and !$maaltijd->getIsGesloten()}
	&nbsp;<button onclick="if(confirm('Weet u zeker dat u deze maaltijd wil sluiten?'))taken_ajax(this, '{$GLOBALS.taken_module}/sluit/{$maaltijd->getMaaltijdId()}', page_reload);">Nu sluiten!</button>
	{/if}
	</h2>
{/if}
{if $maaltijd->getAantalAanmeldingen() > 0}
	{assign var=teller value=1}
	{table_foreach from=$aanmeldingen inner=rows item=aanmelding table_attr='class="aanmeldingen"' cols=2 name=aanmeldingen}
		<div class="nummer">{$teller++}</div></td>
		{if $aanmelding->getLidId()}
		<td class="naam">{$aanmelding->getLid()->getNaamLink($GLOBALS.weergave_ledennamen_maaltijdlijst, 'link')}
			{if $aanmelding->getLid()->getProperty('eetwens') !== ''}<div class="eetwens">{$aanmelding->getLid()->getProperty('eetwens')}</div>{/if}
			{if $aanmelding->getGastenOpmerking() !== ''}<div class="opmerking">Gasten opmerking: {$aanmelding->getGastenOpmerking()}</div>{/if}
		</td>
		<td class="box">{$aanmelding->getSaldoMelding()}</td>
		{elseif $aanmelding->getDoorLidId()}
		<td class="naam">Gast van {$aanmelding->getDoorLid()->getNaamLink($GLOBALS.weergave_ledennamen_maaltijdlijst)}</td>
		<td class="box">-</td>
		{else}
		<td class="naam"></td>
		<td class="box">-</td>
		{/if}
		<td class="geld">&euro;</td>
		<td class="box" style="color: #AAAAAA">m</td>
		<td class="clear">
	{/table_foreach}
{else}
	<p>Nog geen aanmeldingen voor deze maaltijd.</p>
{/if}
<table>
	<tr>
		<td style="width: 50px;">&nbsp;</td>
		<td style="width: 200px;">
			<h3>Maaltijdgegevens</h3>
			<table>
				<tr><td>Inschrijvingen:</td><td>{$maaltijd->getAantalAanmeldingen()}</td></tr>
				<tr><td>Marge:</td><td>{$maaltijd->getMarge()}</td></tr>
				<tr><td>Eters:</td><td>{$eterstotaal}</td></tr>
				<tr><td>Budget koks:</td><td>&euro; {$maaltijd->getBudget()|string_format:"%.2f"}</td></tr>
			</table>
		</td>
		<td style="width: 150px;">&nbsp;</td>
		<td style="width: 500px;">
			<h3>Corvee</h3>
{if $corveetaken}
	{table_foreach from=$corveetaken inner=rows item=taak table_attr='class="corveetaken"' cols=2 name=corveetaken}
			&bullet;&nbsp;
		{if $taak->getLidId()}
			{$taak->getLid()->getNaamLink($GLOBALS.weergave_ledennamen_maaltijdlijst, 'link')}
		{else}
			<i>vacature</i>
		{/if}
		&nbsp;({$taak->getCorveeFunctie()->getNaam()})
	{/table_foreach}
{else}
			<p>Geen corveetaken voor deze maaltijd in het systeem.</p>
{/if}
		</td>
	</tr>
</table>
</body>
</html>{/strip}