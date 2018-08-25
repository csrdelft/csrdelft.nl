<!DOCTYPE html>
<html>
<head>
	@include('head')
</head>
<body class="nav-is-fixed">
<nav class="navbar navbar-dark bg-primary fixed-top">
	<a class="nav-option trigger text-white" href="#zijbalk"><span class="sr-only">Zijbalk openen</span><i
			class="fa fa-lg fa-fw fa-bookmark"></i></a>
	<a class="navbar-brand trigger" href="/">C.S.R. Delft</a>
	<a class="nav-option trigger text-white" href="#search"><span class="sr-only">Zoeken</span><i
			class="fa fa-lg fa-fw fa-search"></i></a>
	<a class="nav-option trigger text-white" href="#menu"><span class="sr-only">Menu</span><i
			class="fa fa-lg fa-fw fa-bars"></i></a>
</nav>
<nav id="zijbalk"
		 @if(CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')!='met pagina mee')
		 class="@if(CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')=='pauper/desktop')desktop-only @endif
		 @if(CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollbalk')=='ja')scroll-hover @endif scroll-fixed dragobject dragvertical" data-scrollfix="{{$scrollfix}}"@endif>
	<a href="/">
		<div class="cd-beeldmerk"></div>
	</a>
	@foreach($zijbalk as $block)
		<div class="blok">@php($block->view())</div>
	@endforeach
</nav>
@php($mainmenu->view())
<main class="cd-main-content">
	<nav class="cd-page-top">
		<div class="breadcrumbs">@stack('breadcrumbs'){{$breadcrumbs}}</div>
	</nav>
	<div class="cd-page-content">
		{!! getMelding() !!}
		@yield('body')
	</div>
	<footer class="cd-footer">
		@php(printDebug())
	</footer>
</main>
<div id="cd-main-overlay">
	@if(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'onontdekt')
		@include('effect/onontdekt')
	@elseif(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'civisaldo')
		@include('effect/civisaldo')
	@endif
</div>
<div id="modal-background" @if(isset($modal)) style="display: block;"@endif></div>
@if(isset($modal))
	@php($modal->view())
@else
	<div id="modal" tabindex="-1"></div>
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'minion') == 'ja')
	@include('effect/minion')
@endif
@if(CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'wolken')
	@include('effect/clouds')
@endif
</body>
</html>
