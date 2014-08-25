<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
<title>{$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</title>
{foreach from=$stylesheets item=sheet}
<link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
{/foreach}
</head>
<body style="font-family: verdana; font-size: 11px; margin-left: 250px;" onload="selectText('lijst');">
<a href="/"><img alt="Beeldmerk van de Vereniging" src="{$CSR_PICS}/layout/beeldmerk.jpg" style="position: absolute; left: 50px;" /></a>
<h1>{$titel} {$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h1>
<h3>Maaltijdprijs: &euro; {$maaltijd->getPrijs()|string_format:"%.2f"}</h3>
<h3>Aanmeldingen: {$maaltijd->getAantalAanmeldingen()} (inclusief gasten)</h3>
<br />
{if $maaltijd->getAantalAanmeldingen() > 0}
<pre id="lijst">
{foreach from=$aanmeldingen item=aanmelding}
{if $aanmelding->getUid()}{$aanmelding->getUid()},{Lid::naamLink($aanmelding->getUid(), 'full', 'plain')}
{else}{$aanmelding->getDoorUid()},Gast van {Lid::naamLink($aanmelding->getDoorUid(), 'full', 'plain')}
{/if}
{/foreach}
</pre>
{else}
<p>Nog geen aanmeldingen voor deze maaltijd.</p>
{/if}
</body>
</html>