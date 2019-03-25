@section('main_tree', $parent)
@foreach($parent->getChildren() as $item)
	@if($item->tekst == 'Personal')
		{include file='menu/personal.tpl' parent=$item}
	@elseif($item->magBekijken)
		@if($item->hasChildren())
			<li class="has-children">
				<a href="#menu">{{$item->tekst}}</a>
				<ul class="is-hidden">
					<li class="go-back"><a href="#menu">{{$item->tekst}}</a></li>
					@yield('main_tree', $item)
					{include file='menu/main_tree.tpl' parent=$item}
				</ul>
			</li>
		@else
			<li><a href="{$item->link}" {if startsWith($item->link, 'http')} target="_blank"{/if}>{$item->tekst}</a></li>
		@endif
	@endif
@endforeach
@endsection


<nav id="menu" class="cd-nav">
	<ul id="cd-primary-nav" class="cd-primary-nav"
			@if (CsrDelft\model\LidInstellingenModel::get('layout', 'fx') != 'nee') style="opacity:0.8;" @endif>
		@if(mag(P_LOGGED_IN))
			<li>
				<a id="cd-main-trigger" class="mobiel-hidden trigger" href="#menu">
					<img id="cd-user-avatar" class="cd-user-avatar"
							 src="/plaetjes/pasfoto/{CsrDelft\model\security\LoginModel::getProfiel()->getPasfotoPath(true)}">
					{!! CsrDelft\model\security\LoginModel::getProfiel()->getNaam('civitas') !!}
					{if $gesprekOngelezen > 0}&nbsp;<span class="badge badge-red"
																								title="{$gesprekOngelezen} ongelezen bericht{if $gesprekOngelezen !== 1}en{/if}">{$gesprekOngelezen}</span>{/if}

				</a>
				<ul class="cd-secondary-nav">
					@yield('main_tree', $parent)
				</ul>
			</li>
			<li class="mobiel-hidden"><a class="trigger" href="#search"><i class="fa fa-search"></i></a></li>
		@else
			<li><a href="/">Log in</a></li>
		@endif
	</ul>
</nav>
<div id="search" class="cd-search">
	{$zoekbalk->view()}
</div>
