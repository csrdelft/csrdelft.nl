<!DOCTYPE html>
<html>
<head lang="nl">
	@include('head')
</head>
<body class="nav-is-fixed" @yield('bodyArgs')>
<nav class="navbar navbar-dark bg-primary fixed-top">
	<a class="nav-option trigger text-white" href="#zijbalk"><span class="sr-only">Zijbalk openen</span><i
			class="fa fa-lg fa-fw fa-bookmark"></i></a>
	<a class="navbar-brand trigger" href="/">C.S.R. Delft</a>
	<a class="nav-option trigger text-white" href="#search"><span class="sr-only">Zoeken</span><i
			class="fa fa-lg fa-fw fa-search"></i></a>
	<a class="nav-option trigger text-white" href="#menu"><span class="sr-only">Menu</span><i
			class="fa fa-lg fa-fw fa-bars"></i></a>
</nav>

<nav id="zijbalk">
	<a href="/">
		<div class="cd-beeldmerk"></div>
	</a>
	@php($zijbalk = \CsrDelft\view\Zijbalk::addStandaardZijbalk(isset($zijbalk) ? $zijbalk : []))
	@foreach($zijbalk as $block)
		<div class="blok">@php($block->view())</div>
	@endforeach
</nav>
@php(view('menu.main', [
  'root' => \CsrDelft\model\MenuModel::instance()->getMenu('main'),
  'favorieten' => \CsrDelft\model\MenuModel::instance()->getMenu(\CsrDelft\model\security\LoginModel::getUid()),
])->view())
<div id="search" class="cd-search">
	@php((new \CsrDelft\view\formulier\InstantSearchForm())->view())
</div>
<main class="cd-main-content">
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
<div id="cd-main-overlay">
	@if(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'onontdekt')
		@include('effect.onontdekt')
	@elseif(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'civisaldo')
		@include('effect.civisaldo')
	@endif
</div>
<div id="modal-background" @if(isset($modal)) style="display: block;"@endif></div>
@if(isset($modal))
	@php($modal->view())
@else
	<div id="modal" tabindex="-1"></div>
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'minion') == 'ja')
	@include('effect.minion')
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'wolken')
	@include('effect.clouds')
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'trein') !== 'nee')
	@include('effect.trein')
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'assistent') !== 'nee')
	<link rel="stylesheet" type="text/css" href="https://gitcdn.xyz/repo/pi0/clippyjs/master/assets/clippy.css">
	<script type="application/javascript">
		const ASSISTENT = '{{ CsrDelft\model\LidInstellingenModel::get('layout', 'assistent') }}';
		const ASSISTENT_GELUIDEN = '{{ \CsrDelft\model\LidInstellingenModel::get('layout', 'assistentGeluiden')}}';
	</script>
	@script('fxclippy.js')
@endif
</body>
</html>
