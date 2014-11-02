<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body>
		{include file='MVC/layout/pagina_header.tpl'}
		<main class="cd-main-content{if LidInstellingen::get('layout', 'bgimages') == 'ja'} bgimages{/if}">
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
						{*$datatable->view()*}
						{$body->view()}
						{if $smarty.const.DEBUG AND (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())}
							<h2 id="mysql_debug_toggle"><a href="#mysql_debug_toggle" onclick="$('#mysql_debug').toggle();">Debug Tonen/Verstoppen</a></h2>
							<div id="mysql_debug" class="pre">{getDebug()}</div>
						{/if}
					</td>
				</tr>
			</table>
		</main>
		{$mainmenu->view()}
		<div id="modal-background"{if isset($modal)} style="display: block;"{/if}></div>
		<div id="modal" class="outer-shadow dragobject savepos" style="top: {$modaltop}px; left: {$modalleft}px;{if isset($modal)} display: block;{/if}">
			{if isset($modal)}
				{$modal->view()}
			{/if}
		</div>
		{include file='MVC/layout/bbcodehulp.tpl'}
		{if isset($minion)}
			{$minion}
		{/if}
	</body>
</html>