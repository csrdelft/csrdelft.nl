{*
menu_beheer_maaltijden.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="beheer-maaltijden-zijbalk" class="maalcie-beheer-menu">
	{assign var="link" value="/maaltijdenbeheer"}
	<div class="zijbalk-kopje item{if (!isset($prullenbak) or !$prullenbak) and (!isset($archief) or !$archief) and maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer maaltijden">Beheer maaltijden</a>
	</div>
	<div class="item{if isset($prullenbak) and $prullenbak and maalcieUrl === $link} active{/if}">»
		<a href="{$link}/prullenbak" title="Open prullenbak">Prullenbak</a>
	</div>
	<div class="item{if isset($archief) and $archief and maalcieUrl === $link} active{/if}">»
		<a href="{$link}/archief" title="Open archief">Archief</a>
	</div>
	<div class="item">»
		<a href="/instellingenbeheer/module/maaltijden" title="Beheer instellingen">Instellingen</a>
	</div>
	{assign var="link" value="/maaltijdenrepetities"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer maaltijdrepetities">Maaltijdrepetities</a>
	</div>
	{assign var="link" value="/maaltijdenabonnementenbeheer"}
	<div class="item{if maalcieUrl === $link} active{/if}">»
		<a href="{$link}" title="Beheer abonnementen">Abonnementen</a>
	</div>
	{if LoginModel::mag('P_MAAL_SALDI')}
		{assign var="link" value="/maaltijdenmaalciesaldi"}
		<div class="item{if maalcieUrl === $link} active{/if}">»
			<a href="{$link}" title="Beheer MaalCie saldi">MaalCie saldi</a>
		</div>
	{/if}
</div>