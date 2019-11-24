<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
</head>
<body class="nav-is-fixed d-flex flex-column h-100" @yield('bodyArgs')>
@php(view('menu.main', [
  'root' => \CsrDelft\model\MenuModel::instance()->getMenu('main'),
  'favorieten' => \CsrDelft\model\MenuModel::instance()->getMenu(\CsrDelft\model\security\LoginModel::getUid()),
])->view())
<main class="container bg-white my-3 flex-shrink-0">
	<nav aria-label="breadcrumb">
		@section('breadcrumbs')
			{!! csr_breadcrumbs(\CsrDelft\model\MenuModel::instance()->getBreadcrumbs($_SERVER['REQUEST_URI'])) !!}
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
	<div class="container-fluid p-3 p-md-5">
		<div class="row">
			<div class="col-12 col-md-auto">
				<img src="/dist/images/beeldmerk.png" width="80" class="d-block mb-2" alt="C.S.R. Logo"/>
				<small class="d-block mb-3 text-muted">© 2006-2019</small>
				@if(!DEBUG) @can(P_ADMIN)
					<small class="cd-block mb-3">
						<a href="{{ commitLink() }}" target="_blank" class="not-external text-muted">{{ commitHash() }}</a>
					</small>
				@endcan @endif
			</div>
			@php($menu = \CsrDelft\model\MenuModel::instance()->getMenu('main'))

			@foreach($menu->getChildren() as $item)
				<div class="col-6 col-md">
					<h5>{{$item->tekst}}</h5>
					<ul class="list-unstyled text-small">
						@foreach($item->getChildren() as $subItem)
							@if($subItem->magBekijken())
								<li><a class="text-muted" href="{{$subItem->link}}">{{$subItem->tekst}}</a></li>
							@endif
						@endforeach
					</ul>
				</div>
			@endforeach
		</div>
	</div>
</footer>
<div id="cd-main-overlay">
</div>
<div id="modal-background" @if(isset($modal)) style="display: block;"@endif></div>
@if(isset($modal))
	@php($modal->view())
@elseif(!isset($modal) && !CsrDelft\model\instellingen\LidToestemmingModel::toestemmingGegeven())
	@php((new \CsrDelft\view\toestemming\ToestemmingModalForm())->view())
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
