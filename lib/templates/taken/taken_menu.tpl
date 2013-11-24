{*
	taken_menu.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="taken-popup-background"{if $popup} style="display: block;"{/if}></div>{if $popup}{$popup->view()}{/if}
<div id="taken-menu">
	<ul class="horizontal">
		{assign var="link" value="maaltijdenketzer"}
		<li{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} class="active"{/if}>
			<a href="/{$link}" title="Maaltijdenketzer">Maaltijdenketzer</a>
		</li>
		{assign var="link" value="maaltijdenabonnementen"}
		<li{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} class="active"{/if}>
			<a href="/{$link}" title="Mijn abonnementen">Mijn abonnementen</a>
		</li>
		{assign var="link" value="corveerooster"}
		<li{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} class="active"{/if}>
			<a href="/{$link}" title="Corveerooster">Corveerooster</a>
		</li>
		{assign var="link" value="corvee"}
		<li{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} class="active"{/if}>
			<a href="/{$link}" title="Mijn corveeoverzicht">Mijn corveeoverzicht</a>
		</li>
		{assign var="link" value="corveevoorkeuren"}
		<li{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} class="active"{/if}>
			<a href="/{$link}" title="Mijn voorkeuren">Mijn voorkeuren</a>
		</li>
	</ul>
</div>
<hr/>
{if $loginlid->hasPermission('P_CORVEE_MOD')}
<div id="beheer-taken-menu" class="block">
{if $loginlid->hasPermission('P_MAAL_MOD')}
	<h1>Beheer</h1>
	{assign var="link" value="maaltijdenbeheer"}
	<div class="item{if !$prullenbak and $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer maaltijden">Maaltijden</a>
	</div>
	<div class="item{if $prullenbak and $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	{assign var="link" value="maaltijdenrepetities"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer maaltijdrepetities">Maaltijdrepetities</a>
	</div>
	{assign var="link" value="maaltijdenabonnementenbeheer"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer abonnementen">Abonnementen</a>
	</div>
	{assign var="link" value="maaltijdeninstellingen"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer instellingen">Instellingen</a>
	</div>
{if $loginlid->hasPermission('P_MAAL_SALDI')}
	{assign var="link" value="maaltijdenmaalciesaldi"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer MaalCie saldi">MaalCie saldi</a>
	</div>
{/if}
	<br />
{/if}
	<h1>Corveebeheer</h1>
	{assign var="link" value="corveebeheer"}
	<div class="item{if !$prullenbak and $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveetaken">Taken</a>
	</div>
	<div class="item{if $prullenbak and $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	{assign var="link" value="corveerepetities"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveerepetities">Corveerepetities</a>
	</div>
	{assign var="link" value="corveefuncties"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveefuncties">Functies & kwalificaties</a>
	</div>
	{assign var="link" value="corveevoorkeurenbeheer"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveevoorkeuren">Voorkeuren</a>
	</div>
	{assign var="link" value="corveepuntenbeheer"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveepunten">Punten</a>
	</div>
	{assign var="link" value="corveevrijstellingen"}
	<div class="item{if $globals.taken_module === $globals.taken_mainmenu|cat:$link} active{/if}">»
		<a href="/{$link}" title="Beheer corveevrijstellingen">Vrijstellingen</a>
	</div>
</div>
{/if}
<table style="width: 100%;"><tr id="taken-melding"><td id="taken-melding-veld">{$melding}</td></tr></table>
<h1>{$kop}</h1>