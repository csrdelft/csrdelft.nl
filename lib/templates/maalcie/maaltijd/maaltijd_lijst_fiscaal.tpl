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
	<body style="font-family: verdana; font-size: 12px; margin-left: 250px;" onload="selectText(document.getElementById('lijst'));">
		<a href="/"><img alt="Beeldmerk van de Vereniging" src="/plaetjes/layout/beeldmerk.jpg" style="position: absolute; left: 50px;" /></a>
		<h1>{$titel} {$maaltijd->getDatum()|date_format:"%Y-%m-%d"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h1>
		<h3>Maaltijdprijs: &euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"}</h3>
		<h3>Aanmeldingen: {$maaltijd->getAantalAanmeldingen()} (inclusief gasten)</h3>
		<br />
		{if $maaltijd->getAantalAanmeldingen() > 0}
<pre id="lijst">{foreach from=$aanmeldingen item=aanmelding}
{if $aanmelding->getUid()}{$aanmelding->getUid()},{Lid::naamLink($aanmelding->getUid(), 'volledig', 'plain')}
{else}{$aanmelding->getDoorUid()},Gast van {Lid::naamLink($aanmelding->getDoorUid(), 'volledig', 'plain')}
{/if}
{/foreach}</pre>
		{else}
			<p>Nog geen aanmeldingen voor deze maaltijd.</p>
		{/if}
	</body>
</html>