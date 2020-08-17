<li class="nav-item d-md-block d-none ml-3">
	<img src="{{\CsrDelft\service\security\LoginService::getProfiel()->getPasfotoPath('vierkant')}}" alt="Pasfoto" class="pasfoto-menu"/>
</li>
<li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle pl-2" href="#" id="menu-personal" role="button" data-toggle="dropdown"
		 aria-haspopup="true" aria-expanded="false">
		{{ \CsrDelft\service\security\LoginService::getProfiel()->getNaam('volledig') }}
	</a>
	<ul class="dropdown-menu" aria-labelledby="menu-personal">
		@if(is_granted('IS_IMPERSONATOR'))
			<li>
				<a href="?_switch_user=_exit" class="dropdown-item error"
					 title="Switch user actie beeindingen">SU {{\CsrDelft\repository\ProfielRepository::getNaam(current_token()->getOriginalToken()->getUserName(), 'civitas')}}</a>
			</li>
		@endif
		<li>
			<a class="dropdown-item" href="/profiel/{{current_account()->uid}}#CiviSaldo"
				 title="Bekijk CiviSaldo historie">
				@php($saldo = \CsrDelft\service\security\LoginService::getProfiel()->getCiviSaldo())
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
