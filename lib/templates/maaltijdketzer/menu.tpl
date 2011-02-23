<div><ul class="horizontal">
{if !$loginlid->hasPermission('P_MAAL_MOD')}
	<li {if $actief == 'maaltijden'}class="active"{/if}>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li {if $actief == 'instellingen'}class="active"{/if}>
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
	<li {if $actief == 'corveepunten'}class="active"{/if}>
		<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
	</li>
{else}
    <li {if $actief == 'maaltijden'}class="active"{/if}>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li {if $actief == 'instellingen'}class="active"{/if}>
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
    <li {if $actief == 'maaltijdbeheer'}class="active"{/if}>
        <a href="/actueel/maaltijden/beheer/" title="Beheer">Maaltijdbeheer</a>
	</li>
	<li {if $actief == 'saldi'}class="active"{/if}>
		<a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
	</li>
</ul>
</div>
<div style="margin-top: 10px;">
<ul class="horizontal">   
    <li {if $actief == 'corveerooster'}class="active"{/if}>
		<a href="/actueel/maaltijden/corveerooster.php" title="Corveerooster">Corveerooster</a>
	</li>        
	<li {if $actief == 'corveepunten'}class="active"{/if}>
		<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
	</li>
	<li {if $actief == 'corveebeheer'}class="active"{/if}>
        <a href="/actueel/maaltijden/corveebeheer/" title="Corveebeheer">Corveebeheer</a>
	</li>
	<li {if $actief == 'corveevoorkeuren'}class="active"{/if}>
        <a href="/actueel/maaltijden/corveevoorkeurenlijst/" title="Corveevoorkeuren">Corveevoorkeuren</a>
	</li>
{/if}
</ul>
</div>

<hr />