<div id="groepen-menu">
	<ul class="horizontal">
		{assign var="link" value="/groepen/commissies"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Commissies</a>
		</li>
		{assign var="link" value="/groepen/besturen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Besturen</a>
		</li>
		{assign var="link" value="/groepen/sjaarcies"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">SjaarCies</a>
		</li>
		{assign var="link" value="/groepen/woonoorden"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Woonoorden</a>
		</li>
		{assign var="link" value="/groepen/werkgroepen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Werkgroepen</a>
		</li>
		{assign var="link" value="/groepen/onderverenigingen"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Onderverenigingen</a>
		</li>
		{assign var="link" value="/groepen/ketzers"}
		<li{if REQUEST_URI === $link} class="active"{/if}>
			<a href="{$link}">Overig</a>
		</li>
	</ul>
</div>
<hr/>
<table><tr id="tr-melding"><td id="td-melding">{getMelding()}</td></tr></table>
<h1>{$titel}</h1>