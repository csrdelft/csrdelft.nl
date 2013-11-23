{*
	taken_menu.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="taken-popup-background"{if $popup} style="display: block;"{/if}></div>{if $popup}{$popup->view()}{/if}
<div id="taken-menu">
	<ul class="horizontal">
		<li{if $module === '/actueel/taken/maaltijden'} class="active"{/if}>
			<a href="/actueel/taken/maaltijden" title="Maaltijdketzers">Maaltijdketzers</a>
		</li>
		<li{if $module === '/actueel/taken/abonnementen'} class="active"{/if}>
			<a href="/actueel/taken/abonnementen" title="Mijn abonnementen">Mijn abonnementen</a>
		</li>
		<li{if $module === '/actueel/taken/corvee/rooster'} class="active"{/if}>
			<a href="/actueel/taken/corvee/rooster" title="Corveerooster">Corveerooster</a>
		</li>
		<li{if $module === '/actueel/taken/corvee'} class="active"{/if}>
			<a href="/actueel/taken/corvee" title="Mijn corveeoverzicht">Mijn corveeoverzicht</a>
		</li>
		<li{if $module === '/actueel/taken/voorkeuren'} class="active"{/if}>
			<a href="/actueel/taken/voorkeuren" title="Mijn voorkeuren">Mijn voorkeuren</a>
		</li>
	</ul>
</div>
<hr/>
{if $loginlid->hasPermission('P_CORVEE_MOD')}
<div id="beheer-taken-menu" class="block">
{if $loginlid->hasPermission('P_MAAL_MOD')}
	<h1>Beheer</h1>
	<div class="item{if !$prullenbak and $module === '/actueel/taken/maaltijdenbeheer'} active{/if}">»
		<a href="/actueel/taken/maaltijdenbeheer" title="Beheer maaltijden">Maaltijden</a>
	</div>
	<div class="item{if $prullenbak and $module === '/actueel/taken/maaltijdenbeheer'} active{/if}">»
		<a href="/actueel/taken/maaltijdenbeheer/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	<div class="item{if $module === '/actueel/taken/maaltijdrepetities'} active{/if}">»
		<a href="/actueel/taken/maaltijdrepetities" title="Beheer maaltijdrepetities">Maaltijdrepetities</a>
	</div>
	<div class="item{if $module === '/actueel/taken/abonnementenbeheer'} active{/if}">»
		<a href="/actueel/taken/abonnementenbeheer" title="Beheer abonnementen">Abonnementen</a>
	</div>
	<div class="item{if $module === '/actueel/taken/instellingen'} active{/if}">»
		<a href="/actueel/taken/instellingen" title="Beheer instellingen">Instellingen</a>
	</div>
{if $loginlid->hasPermission('P_MAAL_SALDI')}
	<div class="item{if $module === '/actueel/taken/maalciesaldi'} active{/if}">»
		<a href="/actueel/taken/maalciesaldi" title="Beheer MaalCie saldi">MaalCie saldi</a>
	</div>
{/if}
	<br />
{/if}
	<h1>Corveebeheer</h1>
	<div class="item{if !$prullenbak and $module === '/actueel/taken/corveebeheer'} active{/if}">»
		<a href="/actueel/taken/corveebeheer" title="Beheer corveetaken">Taken</a>
	</div>
	<div class="item{if $prullenbak and $module === '/actueel/taken/corveebeheer'} active{/if}">»
		<a href="/actueel/taken/corveebeheer/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	<div class="item{if $module === '/actueel/taken/corveerepetities'} active{/if}">»
		<a href="/actueel/taken/corveerepetities" title="Beheer corveerepetities">Corveerepetities</a>
	</div>
	<div class="item{if $module === '/actueel/taken/functies'} active{/if}">»
		<a href="/actueel/taken/functies" title="Beheer corveefuncties">Functies & kwalificaties</a>
	</div>
	<div class="item{if $module === '/actueel/taken/voorkeurenbeheer'} active{/if}">»
		<a href="/actueel/taken/voorkeurenbeheer" title="Beheer corveevoorkeuren">Voorkeuren</a>
	</div>
		<div class="item{if $module === '/actueel/taken/puntenbeheer'} active{/if}">»
		<a href="/actueel/taken/puntenbeheer" title="Beheer corveepunten">Punten</a>
	</div>
	<div class="item{if $module === '/actueel/taken/vrijstellingen'} active{/if}">»
		<a href="/actueel/taken/vrijstellingen" title="Beheer corveevrijstellingen">Vrijstellingen</a>
	</div>
</div>
{/if}
<table style="width: 100%;"><tr id="taken-melding"><td id="taken-melding-veld">{$melding}</td></tr></table>
<h1>{$kop}</h1>