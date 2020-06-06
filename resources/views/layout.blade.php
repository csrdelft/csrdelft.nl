<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
</head>
<body class="nav-is-fixed {{lid_instelling('zijbalk', 'breedte')}}" @yield('bodyArgs')>
@php(view('menu.main', [
  'root' => get_menu('main'),
  'personal' => get_menu('Personal'),
  'favorieten' => get_menu(\CsrDelft\service\security\LoginService::getUid()),
])->view())
<nav id="zijbalk">
	@php($zijbalk = \CsrDelft\view\Zijbalk::addStandaardZijbalk(isset($zijbalk) ? $zijbalk : []))
	@foreach($zijbalk as $block)
		<div class="blok">@php($block->view())</div>
	@endforeach
	@if(!DEBUG) @can(P_ADMIN)
		<div class="commit-hash">
			<a href="{{ commitLink() }}" target="_blank" class="not-external">{{ commitHash() }}</a>
		</div>
	@endcan @endif
</nav>
<main class="container my-3 flex-shrink-0">
	<nav aria-label="breadcrumb">
		@section('breadcrumbs')
			{!! csr_breadcrumbs(get_breadcrumbs($_SERVER['REQUEST_URI'])) !!}
		@show
	</nav>
	<div class="cd-page-content">
		{!! getMelding() !!}
		@yield('content')
	</div>
	<footer class="cd-footer">
		@php(printDebug())
	</footer>
</main>
<footer class="footer mt-auto py-3">
	<div class="container-fluid p-md-5">
		<div class="row">
			<div class="col-12 col-md-auto">
				<img src="/dist/images/beeldmerk.png" width="80" class="d-block mb-2" alt="C.S.R. Logo"/>
				<small class="d-block mb-3 text-muted">© 2006-{{date('Y')}}</small>
				@if(!DEBUG) @can(P_ADMIN)
					<small class="cd-block mb-3">
						<a href="{{ commitLink() }}" target="_blank" class="not-external text-muted">{{ commitHash() }}</a>
					</small>
				@endcan @endif
			</div>
			@foreach(get_menu('main')->children as $item)
				@if($item->magBekijken())
					<div class="col-6 col-md">
						<h5>{{$item->tekst}}</h5>
						<ul class="list-unstyled text-small">
							@foreach($item->children as $subItem)
								@if($subItem->magBekijken())
									<li><a class="text-muted" href="{{$subItem->link}}">{{$subItem->tekst}}</a></li>
								@endif
							@endforeach
						</ul>
					</div>
				@endif
			@endforeach
		</div>
	</div>
</footer>
<div id="modal-background" @if(isset($modal)) style="display: block;"@endif></div>
@if(isset($modal))
	@php($modal->view())
@elseif(!isset($modal) && !toestemming_gegeven())
	@php(toestemming_form()->view())
@else
	<div id="modal" tabindex="-1"></div>
@endif
@if(lid_instelling('layout', 'minion') == 'ja')
	@include('effect.minion')
@endif
@if(lid_instelling('layout', 'fx') == 'onontdekt')
	@include('effect.onontdekt')
@elseif(lid_instelling('layout', 'fx') == 'civisaldo')
	@include('effect.civisaldo')
@elseif(lid_instelling('layout', 'fx') == 'wolken')
	@script('fxclouds.js')
@endif
@if(lid_instelling('layout', 'trein') !== 'nee')
	@include('effect.trein')
@endif
@if(lid_instelling('layout', 'raket') !== 'nee')
	@include('effect.raket')
@endif
@if(lid_instelling('layout', 'assistent') !== 'nee')
	<link rel="stylesheet" type="text/css" href="https://gitcdn.xyz/repo/pi0/clippyjs/master/assets/clippy.css">
	<script type="application/javascript">
		const ASSISTENT = '{{ lid_instelling('layout', 'assistent') }}';
		const ASSISTENT_GELUIDEN = '{{ lid_instelling('layout', 'assistentGeluiden')}}';
	</script>
	@script('fxclippy.js')
@endif
</body>
</html>
