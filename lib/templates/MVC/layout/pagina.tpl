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
							<div id="zijbalk"{if LidInstellingen::get('layout', 'zijbalk') == 'apart scrollen'} scrollfix="{$scrollfix}" class="scroll-fixed dragobject dragvertical"{/if}>
								<a id="cd-logo" href="/"><div id="beeldmerk"></div></a>
									{foreach from=$zijbalk item=block}
									<div class="block">{$block->view()}</div>
								{/foreach}
							</div>
						</td>
					{/if}
					<td id="mainright">
						{*$datatable->view()*}
						{$body->view()}
						{include file='MVC/layout/ubbhulp.tpl'}
						{if isset($minion)}
							{$minion}
						{/if}
					</td>
				</tr>
				{if isset($debug)}
					<tr>
						<td>
							<h2 id="mysql_debug_toggle"><a href="#mysql_debug_toggle" onclick="$('#mysql_debug').toggle();">Debug Tonen/Verstoppen</a></h2>
							<div id="mysql_debug">{$debug}</div>
						</td>
					</tr>
				{/if}
			</table>
		</main>
		{$mainmenu->view()}
		<div id="modal-background"{if isset($modal)} style="display: block;"{/if}></div>
		<div id="modal" class="outer-shadow dragobject" style="top: {$modaltop}px; left: {$modalleft}px;{if isset($modal)} display: block;{/if}">
			{if isset($modal)}
				{$modal->view()}
			{/if}
		</div>
	</body>
</html>