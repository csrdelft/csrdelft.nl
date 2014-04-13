<div id="groepen-menu">
	<ul class="horizontal">
		{assign var="link" value="/commissies"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Commissies</a>
		</li>
		{assign var="link" value="/besturen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Besturen</a>
		</li>
		{assign var="link" value="/sjaarcies"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">SjaarCies</a>
		</li>
		{assign var="link" value="/woonoorden"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Woonoorden</a>
		</li>
		{assign var="link" value="/werkgroepen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Werkgroepen</a>
		</li>
		{assign var="link" value="/onderverenigingen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Onderverenigingen</a>
		</li>
		{assign var="link" value="/ketzers"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Overig</a>
		</li>
	</ul>
</div>
<hr/>
<table style="width: 100%;"><tr id="tr-melding"><td id="td-melding">{$view->getMelding()}</td></tr></table>
<h1>{$view->getTitel()}</h1>