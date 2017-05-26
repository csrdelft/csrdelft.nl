<!DOCTYPE html>
<html class="no-js">
	<head>
		{include file='html_head.tpl'}
	</head>
	<body class="nav-is-fixed">
		<header class="cd-main-header">
			<ul class="cd-header-buttons">
				<li><a class="cd-search-trigger" href="#cd-search">Zoeken<span></span></a></li>
				<li><a class="cd-nav-trigger" href="#cd-primary-nav">Menu<span></span></a></li>
			</ul>
		</header>
		<aside id="cd-zijbalk">
			<a href="/">
				<div class="cd-beeldmerk"></div>
			</a>
			<a class="link zoeken"><i class="fa fa-search"></i></a>
			<a class="link categorie" href="#"><i class="fa fa-home"></i>thuis</a>
			<a class="link categorie" href="#"><i class="fa fa-group"></i>groepen</a>
			<a class="link categorie" href="#"><i class="fa fa-bolt"></i>actueel</a>
			<a class="link categorie" href="#"><i class="fa fa-comments"></i>forum</a>
			<a class="link categorie" href="#"><i class="fa fa-paper-plane"></i>communicatie</a>

			<div class="persoonlijk">
				<img class="foto" src="/plaetjes/pasfoto/1345.vierkant.png"/>
				<div class="naam">{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('civitas')}</div>
				<div class="saldo-titel">saldo</div>
                {assign var=saldo value=CsrDelft\model\security\LoginModel::getProfiel()->getCiviSaldo()}
				<div class="saldo-bedrag{if $saldo < 0} staatrood{/if}">
					&euro; {$saldo|number_format:2:",":"."}
				</div>
			</div>
		</aside>
		<nav class="cd-page-top">
			<div class="breadcrumbs">{$breadcrumbs}</div>
		</nav>
		<main>
			<div class="foto"></div>
			<section class="forum">
				<h1>forum</h1>
				<ul>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
					<li><a href="#"class="forum-draad-link">Broedersportdag en BBBQ</a> <a href="#" class="forum-categorie-link">C.S.R.-zaken</a> 9 minuten geleden <img class="pasfoto-klein" src="/plaetjes/pasfoto/1529.vierkant.png"></li>
				</ul>
			</section>
			<section>
				<h1>mededelingen</h1>
			</section>
			<section>
				<h1>agenda</h1>
			</section>
			<section>
				<h1>jarig</h1>
			</section>




			{*{$body->view()}*}
			{*<footer class="cd-footer">*}
				{*{printDebug()}*}
			{*</footer>*}
		</main>
		{*{$mainmenu->view()}*}
		{*<div id="cd-main-overlay">*}
			{*{if CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'onontdekt'}*}
				{*{include file='layout/fx-onontdekt.tpl'}*}
			{*{elseif CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'civisaldo'}*}
				{*{include file='layout/fx-civisaldo.tpl'}*}
			{*{/if}*}
		{*</div>*}
		<div id="modal-background"{if isset($modal)} style="display: block;"{/if}></div>
		{if isset($modal)}
			{$modal->view()}
		{else}
			<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1"></div>
		{/if}
		{if isset($minion)}
			{$minion}
		{/if}
		{if CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'wolken'}
			{include file='layout/fx-clouds.tpl'}
		{/if}
	</body>
</html>
