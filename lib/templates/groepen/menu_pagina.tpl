<div id="groepen-menu">
	<ul class="horizontal">
		{assign var="link" value="/groep/commissies"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Commissies</a>
		</li>
		{assign var="link" value="/groep/besturen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Besturen</a>
		</li>
		{assign var="link" value="/groep/activiteiten"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Activiteiten</a>
		</li>
		{assign var="link" value="/groep/woonoorden"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Woonoorden</a>
		</li>
		{assign var="link" value="/groep/werkgroepen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Werkgroepen</a>
		</li>
		{assign var="link" value="/groep/onderverenigingen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Onderverenigingen</a>
		</li>
		{assign var="link" value="/groep/overig"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Overig</a>
		</li>
	</ul>
</div>
<hr/>
<table><tr id="tr-melding"><td id="td-melding">{getMelding()}</td></tr></table>
<h1>{$titel}</h1>