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
<h3>Aanmeldingen: {$maaltijd->getAantalAanmeldingen()} (inclusief gasten)</h3>
<br />
{if $maaltijd->getAantalAanmeldingen() > 0}
<pre id="lijst">
{foreach from=$aanmeldingen key=uid item=naam}
{$uid|truncate:4:""},{$naam}
{/foreach}
</pre>
{else}
<p>Nog geen aanmeldingen voor deze maaltijd.</p>
{/if}
</body>
</html>