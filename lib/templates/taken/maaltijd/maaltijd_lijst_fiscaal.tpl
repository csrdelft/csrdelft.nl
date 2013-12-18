<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
<title>{$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</title>
<script type="text/javascript" src="/layout/js/csrdelft.js"></script>
</head>
<body style="font-family: verdana; font-size: 11px; margin-left: 250px;" onload="selectText('lijst');">
<img alt="Beeldmerk van de Vereniging" src="{$csr_pics}/layout/beeldmerk.jpg" style="position: absolute; left: 50px;" />
<h1>{$kop} {$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h1>
<h3>Maaltijdprijs: &euro; {$maaltijd->getPrijs()|string_format:"%.2f"}</h3>
<br />
<pre id="lijst">
{if $maaltijd->getAantalAanmeldingen() > 0}
{foreach from=$aanmeldingen item=aanmelding}
{if $aanmelding->getLidId()}
{$aanmelding->getLidId()},{$aanmelding->getLid()->getNaamLink()}
{else}
{$aanmelding->getDoorLidId()},Gast van {$aanmelding->getDoorLid()->getNaamLink()}
{/if}
{/foreach}
{else}
Nog geen aanmeldingen voor deze maaltijd.
{/if}
</pre>
</body>
</html>