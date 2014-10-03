{*
	menu_pagina.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="maalcie-menu">
	<ul class="horizontal">
		{assign var="link" value="/maaltijdenketzer"}
		<li{if Instellingen::get('taken', 'url') === $link} class="active"{/if}>
			<a href="{$link}">Maaltijdenketzer</a>
		</li>
		{assign var="link" value="/maaltijdenabonnementen"}
		<li{if Instellingen::get('taken', 'url') === $link} class="active"{/if}>
			<a href="{$link}">Mijn abonnementen</a>
		</li>
		{assign var="link" value="/corveerooster"}
		<li{if Instellingen::get('taken', 'url') === $link} class="active"{/if}>
			<a href="{$link}">Corveerooster</a>
		</li>
		{assign var="link" value="/corvee"}
		<li{if Instellingen::get('taken', 'url') === $link} class="active"{/if}>
			<a href="{$link}">Mijn corveeoverzicht</a>
		</li>
		{assign var="link" value="/corveevoorkeuren"}
		<li{if Instellingen::get('taken', 'url') === $link} class="active"{/if}>
			<a href="{$link}">Mijn voorkeuren</a>
		</li>
	</ul>
</div>
<hr/>
<table><tr id="maalcie-melding"><td id="maalcie-melding-veld">{SimpleHtml::getMelding()}</td></tr></table>
<h1>{$titel}</h1>