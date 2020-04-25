<!DOCTYPE html>
<html lang="nl">

<head>
	<meta name="description" content="{{instelling('stek', 'beschrijving')}}">
	<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE"/>
	<meta property="og:url" content="{{CSR_ROOT}}{{REQUEST_URI}}"/>
	<meta property="og:title" content="C.S.R. Delft | @yield('titel')"/>
	<meta property="og:locale" content="nl_nl"/>
	<meta property="og:image" content="{{CSR_ROOT}}/images/beeldmerk.png"/>
	<meta property="og:description" content="{{instelling('stek', 'beschrijving')}}"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{!! csrfMetaTag() !!}
	<title>C.S.R. Delft - @yield('titel')</title>
	<link rel="shortcut icon" href="{{CSR_ROOT}}/images/favicon.ico"/>
	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml"
				href="{{CSR_ROOT}}/forum/rss.xml"/>
	@yield('styles')
	@script('extern.js')
</head>

<body>
<script>document.body.classList.add('is-loading');</script>
<!-- Page Wrapper -->
<div id="page-wrapper">

	<!-- Header -->
	<header id="header" class="alt">
		<nav>
{{--			<a class="nav-link" href="/owee">Owee</a>--}}
			<span class="dropdown-menu">
				<a href="/vereniging" class="nav-link dropdown-link">Vereniging</a>
				<span class="dropdown">
					<a href="/vereniging/geloof">Geloof</a>
					<a href="/vereniging/vorming">Vorming</a>
					<a href="/vereniging/gezelligheid">Gezelligheid</a>
					<a href="/vereniging/sport">Sport</a>
					<a href="/vereniging/ontspanning">Ontspanning</a>
				</span>
			</span>
			<a class="nav-link" href="/fotoalbum/Publiek">Foto's</a>
			<span class="dropdown-menu">
			<a class="nav-link" href="/forum">Forum</a>
				<span class="dropdown">
					<a href="/forum/deel/12">Kamers zoeken en aanbieden</a>
				</span>
			</span>
			<a class="nav-link" href="/lidworden">Lid worden?</a>
			<span class="dropdown-menu">
			<a class="nav-link" href="/contact">Contact</a>
				<span class="dropdown">
					<a href="/contact/bedrijven">Bedrijven</a>
				</span>
			</span>
		</nav>
		<nav>
			@section('loginbutton')
				<a class="nav-link inloggen" href="#login">Inloggen</a>
			@show
		</nav>
	</header>

@section('loginpopup')
	<!-- Loginform -->
		<nav id="login">
			<a href="#_" class="overlay"></a>
			<div class="inner">
				<h2>Inloggen</h2>
				@php((new \CsrDelft\view\login\LoginForm())->view())
				<a href="#_" class="close">Close</a>
			</div>
		</nav>
@show

<!-- Menu -->
	<nav id="menu">
		<a href="#_" class="overlay"></a>
		<div class="inner">
			<h2>Menu</h2>
			<ul class="links">
				<li><a href="/">Begin</a></li>
				<li><a href="/vereniging">Informatie over C.S.R.</a></li>
				<li><a href="/fotoalbum">Fotoalbum</a></li>
				<li><a href="/forum">Forum</a></li>
				<li><a href="/forum/deel/12">Kamers zoeken/aanbieden</a></li>
				<li><a href="/contact">Contactinformatie</a></li>
				<li><a href="/contact/bedrijven">Bedrijven</a></li>
			</ul>
			<a href="#_" class="close">Close</a>
		</div>
	</nav>

@section('body')
	<!-- Banner -->
		<section id="banner-small">
			<div class="inner">
				<a href="/"><img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging" height="140">
					<h2>C.S.R. Delft</h2></a>
			</div>
		</section>

		<!-- Wrapper -->
		<section id="wrapper">
			<section class="wrapper detail kleur1">
				<div class="inner">
					<div class="content">
						@yield('content')
					</div>
				</div>
			</section>
			<section id="footer">
				<div class="inner">
					<ul class="copyright">
						<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
								href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
					</ul>
				</div>
			</section>
		</section>
	@show
</div>
</body>
</html>
