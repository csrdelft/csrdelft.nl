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
							<div id="zijbalk"{if LidInstellingen::get('zijbalk', 'scrollen') == 'apart scrollen'} class="scroll-fixed dragobject dragvertical" scrollfix="{$scrollfix}"{/if}>
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
						{if isset($debug)}
							<h2 id="mysql_debug_toggle"><a href="#mysql_debug_toggle" onclick="$('#mysql_debug').toggle();">Debug Tonen/Verstoppen</a></h2>
							<div id="mysql_debug">{$debug}</div>
						{/if}
					</td>
				</tr>
			</table>
		</main>
		{$mainmenu->view()}
		<div id="modal-background"{if isset($modal)} class="block"{/if}></div>
		<div id="modal" class="outer-shadow dragobject savepos" style="top: {$modaltop}px; left: {$modalleft}px;{if isset($modal)} display: block;{/if}">
			{if isset($modal)}
				{$modal->view()}
			{/if}
		</div>
		{include file='MVC/layout/ubbhulp.tpl'}
		{if isset($minion)}
			{$minion}
		{/if}
	</body>
</html>