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
		<main class="cd-main-content">
			<table id="maintable">
				<tr>
					{if is_array($zijbalk)}
						<td id="mainleft">
							<div id="zijbalk"{if LidInstellingen::get('zijbalk', 'scrollen') != 'met pagina mee'} class="{if LidInstellingen::get('zijbalk', 'scrollen') == 'pauper/desktop'}desktop-only {/if}{if LidInstellingen::get('zijbalk', 'scrollbalk') == 'ja'}scroll-hover {/if}scroll-fixed dragobject dragvertical" data-scrollfix="{$scrollfix}"{/if}>
								<a id="cd-logo" href="/"><div id="beeldmerk"></div></a>
									{foreach from=$zijbalk item=block}
									<div class="block">{$block->view()}</div>
								{/foreach}
								{if LidInstellingen::get('zijbalk', 'scrollen') == 'met pagina mee'}<br /><br /><br />{/if}
							</div>
						</td>
					{/if}
					<td id="mainright">
						<div id="page-top">
							<div class="breadcrumbs">{$breadcrumbs}</div>
						</div>
						{$body->view()}
						{printDebug()}
					</td>
				</tr>
			</table>
			<div id="cd-main-overlay" class="cd-main-overlay"></div>
		</main>
		{$mainmenu->view()}
		<div id="modal-background" {if isset($modal)} style="display: block;"{/if}></div>
		{if isset($modal)}
			{$modal->view()}
		{else}
			<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1"></div>
		{/if}
		{if isset($minion)}
			{$minion}
		{/if}
		{if LidInstellingen::get('layout', 'sfx') == 'wolken'}
			{include file='csrdelft/sfx-clouds.tpl'}
		{/if}
	</body>
</html>