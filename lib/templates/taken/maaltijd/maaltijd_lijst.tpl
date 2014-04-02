<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>{strip}
<head>
	<title>{$view->getTitel()} {$maaltijd->getDatum()|date_format:"%A %e %B"}</title>
{foreach from=$view->getStylesheets() item=sheet}
	<link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
{/foreach}
{foreach from=$view->getScripts() item=script}
	<script type="text/javascript" src="{$script.naam}?{$script.datum}"></script>
{/foreach}
</head>
<body>
<a href="/"><img alt="Beeldmerk van de Vereniging" src="{$CSR_PICS}/layout/beeldmerk.jpg" style="float: right; padding: 0px 50px;" /></a>
<h1>{$view->getTitel()} op {$maaltijd->getDatum()|date_format:"%A %e %B %Y"}</h1>
<div class="header">{Instellingen::get('maaltijden', 'maaltijdlijst_tekst')|replace:'MAALTIJDPRIJS':$prijs}</div>
{if !$maaltijd->getIsGesloten()}
	<h2 id="maaltijd-gesloten" style="color: red">De inschrijving voor deze maaltijd is nog niet gesloten
	{if !$maaltijd->getIsVerwijderd() and !$maaltijd->getIsGesloten()}
	&nbsp;<button href="{Instellingen::get('taken', 'url')}/sluit/{$maaltijd->getMaaltijdId()}" class="knop post confirm" title="Deze maaltijd sluiten">Nu sluiten!</button>
	{/if}
	</h2>
{/if}
{if $maaltijd->getAantalAanmeldingen() > 0}
	{assign var=teller value=1}
	{table_foreach from=$aanmeldingen inner=rows item=aanmelding table_attr='class="aanmeldingen"' cols=2 name=aanmeldingen}
		<div class="nummer">{$teller++}</div></td>
		{if $aanmelding->getLidId()}
		<td class="naam">{$aanmelding->getLid()->getNaamLink(Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'))}
			{if $aanmelding->getLid()->getProperty('eetwens') !== ''}<div class="eetwens">{$aanmelding->getLid()->getProperty('eetwens')}</div>{/if}
			{if $aanmelding->getGastenOpmerking() !== ''}<div class="opmerking">Gasten opmerking: {$aanmelding->getGastenOpmerking()}</div>{/if}
		</td>
		<td class="box">{$aanmelding->getSaldoMelding()}</td>
		{elseif $aanmelding->getDoorLidId()}
		<td class="naam">Gast van {$aanmelding->getDoorLid()->getNaamLink(Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), 'plain')}</td>
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
			{$taak->getLid()->getNaamLink(Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'))}
		{else}
			<i>vacature</i>
		{/if}
		&nbsp;({$taak->getCorveeFunctie()->naam})
	{/table_foreach}
{else}
			<p>Geen corveetaken voor deze maaltijd in het systeem.</p>
{/if}
		</td>
	</tr>
</table>
</body>
</html>{/strip}