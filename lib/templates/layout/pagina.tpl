<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body class="nav-is-fixed">
		<nav class="navbar navbar-dark bg-primary fixed-top">
			<a class="nav-option trigger text-white" href="#zijbalk"><span class="sr-only">Zijbalk openen</span><i class="fa fa-lg fa-fw fa-bookmark"></i></a>
			<a class="navbar-brand trigger" href="/">C.S.R. Delft</a>
			<a class="nav-option trigger text-white" href="#search"><span class="sr-only">Zoeken</span><i class="fa fa-lg fa-fw fa-search"></i></a>
			<a class="nav-option trigger text-white" href="#menu"><span class="sr-only">Menu</span><i class="fa fa-lg fa-fw fa-bars"></i></a>
		</nav>
		<nav id="zijbalk"{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')!='met pagina mee'} class="{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')=='pauper/desktop'}desktop-only {/if}{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollbalk')=='ja'}scroll-hover {/if}scroll-fixed dragobject dragvertical" data-scrollfix="{$scrollfix}"{/if}>
			<a href="/">
				<div class="cd-beeldmerk"></div>
			</a>
			{foreach from=$zijbalk item=blok}
				<div class="blok">{$blok->view()}</div>
			{/foreach}
		</nav>
		{$mainmenu->view()}
		<main class="cd-main-content">
			<nav class="cd-page-top">
				<div class="breadcrumbs">{$breadcrumbs}</div>
			</nav>
			<div class="cd-page-content">
				{getMelding()}
				{$body->view()}
			</div>
			<footer class="cd-footer">
				{printDebug()}
			</footer>
		</main>
		<div id="cd-main-overlay">
			{if CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'onontdekt'}
				{include file='layout/fx-onontdekt.tpl'}
			{elseif CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'civisaldo'}
				{include file='layout/fx-civisaldo.tpl'}
			{/if}
		</div>
		<div id="modal-background"{if isset($modal)} style="display: block;"{/if}></div>
		{if isset($modal)}
			{$modal->view()}
		{else}
			<div id="modal" tabindex="-1"></div>
		{/if}
		{if isset($minion)}
			{$minion}
		{/if}
		{if CsrDelft\model\LidInstellingenModel::get('layout', 'fx') == 'wolken'}
			{include file='layout/fx-clouds.tpl'}
		{/if}
	</body>
</html>
