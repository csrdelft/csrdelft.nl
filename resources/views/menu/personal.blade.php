<li class="nav-item d-md-block d-none ml-3">
	<img src="{{\CsrDelft\model\security\LoginModel::getProfiel()->getPasfotoPath('vierkant')}}" alt="Pasfoto" class="pasfoto-menu"/>
</li>
<li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle pl-2" href="#" id="menu-personal" role="button" data-toggle="dropdown"
		 aria-haspopup="true" aria-expanded="false">
		{{ CsrDelft\model\security\LoginModel::getProfiel()->getNaam('volledig') }}
	</a>
	<ul class="dropdown-menu" aria-labelledby="menu-personal">
		@if(\CsrDelft\model\security\LoginModel::instance()->isSued())
			<li>
				<a href="/endsu" class="dropdown-item error"
					 title="Switch user actie beeindingen">SU {{\CsrDelft\repository\ProfielRepository::getNaam(CsrDelft\model\security\LoginModel::getSuedFrom()->uid, 'civitas')}}</a>
			</li>
		@endif
		<li>
			<a class="dropdown-item" href="/profiel/{{CsrDelft\model\security\LoginModel::getUid()}}#CiviSaldo"
				 title="Bekijk CiviSaldo historie">
				@php($saldo = \CsrDelft\model\security\LoginModel::getProfiel()->getCiviSaldo())
				@if($saldo < 0)
					CiviSaldo: <span class="staatrood">&euro; {{number_format($saldo, 2, ',', '.')}}</span>
				@else
					CiviSaldo: <span>&euro; {{number_format($saldo, 2, ',', '.')}}</span>
				@endif
			</a>
		</li>
		@include('menu.sub_tree', ['parent' => $parent, 'dropleft' => true])
	</ul>
</li>
