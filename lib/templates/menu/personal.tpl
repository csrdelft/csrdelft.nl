<li class="has-children{if LoginModel::instance()->isSued()} sued{/if}">
	<a href="#0">{LoginModel::getProfiel()->getNaam('volledig')}</a>
	<ul class="is-hidden">
		<li class="go-back"><a href="#0">{LoginModel::getProfiel()->getNaam('volledig')}</a></li>
	{if LoginModel::instance()->isSued()}
		<li><a href="/endsu" class="error" title="Switch user actie beeindingen">SU {LoginModel::getSuedFrom()->getNaam('civitas')}</a></li>
	{/if}
		<li>
			<a href="/profiel/{LoginModel::getUid()}#SocCieSaldo" title="Bekijk SocCie saldo historie">
				{assign var=saldo value=LoginModel::getProfiel()->getSoccieSaldo()}
				SocCie: <span{if $saldo < 0} class="staatrood"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
			</a>
		</li>
		<li>
			<a href="/profiel/{LoginModel::getUid()}#MaalCieSaldo" title="Bekijk MaalCie saldo historie">
				{assign var=saldo value=LoginModel::getProfiel()->getMaalcieSaldo()}
				MaalCie: <span{if $saldo < 0} class="staatrood"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
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
