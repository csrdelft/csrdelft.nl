<li class="has-children{if CsrDelft\model\security\LoginModel::instance()->isSued()} sued{/if}">
	<a href="#0">{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('volledig')}</a>
	<ul class="is-hidden">
		<li class="go-back"><a href="#0">{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('volledig')}</a></li>
	{if Auth::isSued()}
		<li><a href="/account/endsu" class="error" title="Switch user actie beeindingen">SU {Auth::suedFrom()->profiel->getNaam('civitas')}</a></li>
	{/if}
		<li><a href="/gesprekken" title="{$gesprekOngelezen} ongelezen bericht{if $gesprekOngelezen !== 1}en{/if}">Gesprekken{if $gesprekOngelezen > 0}&nbsp;<span class="badge">{$gesprekOngelezen}</span>{/if}</a></li>
		<li>
			<a href="/profiel/{CsrDelft\model\security\LoginModel::getUid()}#CiviSaldo" title="Bekijk CiviSaldo historie">
				{assign var=saldo value=CsrDelft\model\security\LoginModel::getProfiel()->getCiviSaldo()}
				CiviSaldo: <span{if $saldo < 0} class="staatrood"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
			</a>
		</li>
		<li class="has-children">
			<a href="#0">Favorieten</a>
			<ul class="is-hidden">
				<li class="go-back"><a href="#0">Favorieten</a></li>
				{include file='menu/main_tree.tpl' parent=$favorieten}
			</ul>
		</li>
		<li><a href="/menubeheer/toevoegen/favoriet" class="post popup addfav" onclick="$('.cd-nav-trigger').click();" title="Huidige pagina toevoegen aan favorieten">Favoriet toevoegen</a></li>
		{include file='menu/main_tree.tpl' parent=$item}
	</ul>
</li>
