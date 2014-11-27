<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body>
		{include file='MVC/layout/pagina_header.tpl'}
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
						<div id="page-top"><div class="breadcrumbs">{$breadcrumbs}</div></div>
						{$body->view()}
						{if $smarty.const.DEBUG AND (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())}
							<div><a id="mysql_debug_toggle" onclick="$(this).replaceWith($('#mysql_debug').toggle());">DEBUG</a></div>
						{/if}
					</td>
				</tr>
			</table>
		</main>
		{$mainmenu->view()}
		{if isset($minion)}
			{$minion}
		{/if}
		{if $smarty.const.DEBUG AND (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())}
			<div id="mysql_debug" class="pre">{getDebug()}</div>
		{/if}
		<div id="modal-background"{if isset($modal)} style="display: block;"{/if}></div>
		{if isset($modal)}
			{$modal->view()}
		{else}
			<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1"></div>
		{/if}
	</body>
</html>