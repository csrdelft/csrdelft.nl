<!DOCTYPE html>
<html>
	<head>
		<title>{$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</title>
		{foreach from=$stylesheets item=sheet}
			<link rel="stylesheet" href="{$sheet}" type="text/css" />
		{/foreach}
		{foreach from=$scripts item=script}
			<script type="text/javascript" src="{$script}"></script>
		{/foreach}
	</head>
	<body style="font-family: verdana; font-size: 11px; margin-left: 250px;" onload="selectText('lijst');">
		<a href="/"><img alt="Beeldmerk van de Vereniging" src="{$CSR_PICS}/layout/beeldmerk.jpg" style="position: absolute; left: 50px;" /></a>
		<h1>{$titel} {$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h1>
		<h2>Maaltijdprijs: &euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"}</h2>
		<h2>Aanmeldingen: {$maaltijd->getAantalAanmeldingen()} (inclusief gasten)</h2>
		<br />
		{if $maaltijd->getAantalAanmeldingen() > 0}
<pre id="lijst">{foreach from=$aanmeldingen item=aanmelding}
{if $aanmelding->getUid()}{$aanmelding->getUid()},{Lid::naamLink($aanmelding->getUid(), 'full', 'plain')}
{else}{$aanmelding->getDoorUid()},Gast van {Lid::naamLink($aanmelding->getDoorUid(), 'full', 'plain')}
{/if}
{/foreach}</pre>
		{else}
			<p>Nog geen aanmeldingen voor deze maaltijd.</p>
		{/if}
	</body>
</html>