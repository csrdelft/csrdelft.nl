{*
	menu_beheer_corvee.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="beheer-corvee-zijbalk" class="maalcie-beheer-menu">
	{assign var="link" value="/corveebeheer"}
	<div class="zijbalk-kopje item{if (!isset($prullenbak) or !$prullenbak) and maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveetaken">Corveebeheer</a>
	</div>
	<div class="item{if isset($prullenbak) and $prullenbak and maalcieUrl === $link} active{/if}">»
		<a href="{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	{assign var="link" value="/corveerepetities"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveerepetities">Corveerepetities</a>
	</div>
	{assign var="link" value="/corveefuncties"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveefuncties">Functies & kwalificaties</a>
	</div>
	<div class="item">»
		<a href="/instellingenbeheer/module/corvee" title="Beheer instellingen">Instellingen</a>
	</div>
	{assign var="link" value="/corveevoorkeurenbeheer"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveevoorkeuren">Voorkeuren</a>
	</div>
	{assign var="link" value="/corveepuntenbeheer"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveepunten">Punten</a>
	</div>
	{assign var="link" value="/corveevrijstellingen"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveevrijstellingen">Vrijstellingen</a>
	</div>
</div>