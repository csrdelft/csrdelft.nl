<!DOCTYPE html>
<html>
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
		<main class="cd-main-content">
			<div id="cd-zijbalk"{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')!='met pagina mee'} class="{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollen')=='pauper/desktop'}desktop-only {/if}{if CsrDelft\model\LidInstellingenModel::get('zijbalk', 'scrollbalk')=='ja'}scroll-hover {/if}scroll-fixed dragobject dragvertical" data-scrollfix="{$scrollfix}"{/if}>
				<a href="/">
					<div class="cd-beeldmerk"></div>
				</a>
				{foreach from=$zijbalk item=blok}
					<div class="blok">{$blok->view()}</div>
				{/foreach}
			</div>
			<nav class="cd-page-top">
				<div class="breadcrumbs">{$breadcrumbs}</div>
			</nav>
			<div class="cd-page-content">
				{$body->view()}
			</div>
			<footer class="cd-footer">
				{printDebug()}
			</footer>
		</main>
		{$mainmenu->view()}
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
