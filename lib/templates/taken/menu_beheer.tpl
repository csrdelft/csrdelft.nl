{*
	menu_beheer.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if $loginlid->hasPermission('P_CORVEE_MOD')}
<div id="beheer-taken-menu" class="block">
	<br />
{if $loginlid->hasPermission('P_MAAL_MOD')}
	<h1>Beheer</h1>
	{assign var="link" value="/maaltijdenbeheer"}
	<div class="item{if (!isset($prullenbak) or !$prullenbak) and (!isset($archief) or !$archief) and $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer maaltijden">Maaltijden</a>
	</div>
	<div class="item{if isset($prullenbak) and $prullenbak and $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	<div class="item{if isset($archief) and $archief and $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}/archief" title="Open archief">Archief</a>
	</div>
	<div class="item">»
		<a href="/instellingenbeheer/module/maaltijden" title="Beheer instellingen">Instellingen</a>
	</div>
	{assign var="link" value="/maaltijdenrepetities"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer maaltijdrepetities">Maaltijdrepetities</a>
	</div>
	{assign var="link" value="/maaltijdenabonnementenbeheer"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer abonnementen">Abonnementen</a>
	</div>
{if $loginlid->hasPermission('P_MAAL_SALDI')}
	{assign var="link" value="/maaltijdenmaalciesaldi"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer MaalCie saldi">MaalCie saldi</a>
	</div>
{/if}
	<br />
{/if}
	<h1>Corveebeheer</h1>
	{assign var="link" value="/corveebeheer"}
	<div class="item{if (!isset($prullenbak) or !$prullenbak) and $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveetaken">Taken</a>
	</div>
	<div class="item{if isset($prullenbak) and $prullenbak and $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	{assign var="link" value="/corveerepetities"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveerepetities">Corveerepetities</a>
	</div>
	{assign var="link" value="/corveefuncties"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveefuncties">Functies & kwalificaties</a>
	</div>
	<div class="item">»
		<a href="/instellingenbeheer/module/corvee" title="Beheer instellingen">Instellingen</a>
	</div>
	{assign var="link" value="/corveevoorkeurenbeheer"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveevoorkeuren">Voorkeuren</a>
	</div>
	{assign var="link" value="/corveepuntenbeheer"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveepunten">Punten</a>
	</div>
	{assign var="link" value="/corveevrijstellingen"}
	<div class="item{if $instellingen->get('taken', 'url') === $link} active{/if}">»
		<a href="{$link}" title="Beheer corveevrijstellingen">Vrijstellingen</a>
	</div>
</div>
{/if}