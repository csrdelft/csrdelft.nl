<li class="has-children @if(CsrDelft\model\security\LoginModel::instance()->isSued()) sued @endif">
	<a href="#menu">{{ CsrDelft\model\security\LoginModel::getProfiel()->getNaam('volledig') }}</a>
	<ul class="is-hidden">
		<li class="go-back"><a class="trigger" href="#menu">{{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('volledig')}}</a></li>
		@if(\CsrDelft\model\security\LoginModel::instance()->isSued())
		<li><a href="/endsu" class="error" title="Switch user actie beeindingen">SU {{CsrDelft\model\ProfielModel::getNaam(CsrDelft\model\security\LoginModel::getSuedFrom()->uid, 'civitas')}}</a></li>
		@endif
		<li>
			<a href="/profiel/{{CsrDelft\model\security\LoginModel::getUid()}}#CiviSaldo" title="Bekijk CiviSaldo historie">
				@php($saldo = \CsrDelft\model\security\LoginModel::getProfiel()->getCiviSaldo())
				@if($saldo < 0)
					CiviSaldo: <span class="staatrood">&euro; {{number_format($saldo, 2, ',', '.')}}</span>
				@else
					CiviSaldo: <span>&euro; {{number_format($saldo, 2, ',', '.')}}</span>
				@endif
			</a>
		</li>
		<li class="has-children">
			<a href="#menu">Favorieten</a>
			<ul class="is-hidden">
				<li class="go-back"><a href="#menu">Favorieten</a></li>
				@include('menu.main_tree', ['parent' => $favorieten])
			</ul>
		</li>
		<li><a href="/menubeheer/toevoegen/favoriet" class="post popup addfav" title="Huidige pagina toevoegen aan favorieten">Favoriet toevoegen</a></li>
		@include('menu.main_tree', ['parent' => $parent])
	</ul>
</li>
