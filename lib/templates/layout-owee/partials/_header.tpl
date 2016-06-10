<!DOCTYPE html>
<html>

<head>
	{include file='html_head.tpl'}
  <!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
	<link rel="stylesheet" href="assets/css/main.css" />
	<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
	<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
</head>

<body>
<!-- Page Wrapper -->
<div id="page-wrapper">

	<!-- Header -->
	<header id="header" class="alt">
		<h1><a href="/">C.S.R. in de OWee</a></h1>
		<nav>
			<a class="inloggen" href="#login">Inloggen</a>
			<a href="#menu">Menu</a>
		</nav>
	</header>

	<!-- Loginform -->
	<nav id="login">
		<div class="inner">
			<h2>Inloggen</h2>
			{$loginform->view()}
		</div>
	</nav>

	<!-- Menu -->
	<nav id="menu">
		<div class="inner">
			<h2>Menu</h2>
			<ul class="links">
				<li><a href="/">Begin</a></li>
				<li><a href="/vereniging">Informatie over C.S.R.</a></li>
				<li><a href="/fotoalbum">Fotoalbum</a></li>
				<li><a href="/forum">Forum</a></li>
				<li><a href="/forum/deel/12">Kamers zoeken/aanbieden</a></li>
				<li><a href="/contact">Contactinformatie</a></li>
				<li><a href="/contact/sponsoring">Bedrijven</a></li>
			</ul>
			<a href="#" class="close">Close</a>
		</div>
	</nav>
