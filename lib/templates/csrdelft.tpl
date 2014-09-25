<!DOCTYPE html>
<html>
	<head>
		{include file='csrdelft_head.tpl'}
	</head>
	<body>
		<div id="popup-background"{if isset($popup)} style="display: block;"{/if}></div>
		<div id="header">{$mainmenu->view()}</div>
		<table id="main">
			<tr>
				{if is_array($zijkolom)}
					<td id="mainleft">
						<div id="zijkolom"{if LidInstellingen::get('layout', 'zijkolom') == 'fixeer'} class="scroll-fix"{/if}>
							{foreach from=$zijkolom item=block}
								<div class="block">{$block->view()}</div>
							{/foreach}
						</div>
					</td>
				{/if}
				<td id="mainright">
					<div id="popup" class="outer-shadow dragobject" style="top: {$popuptop}px; left: {$popupleft}px;{if isset($popup)} display: block;{/if}">
						{if isset($popup)}
							{$popup->view()}
						{/if}
					</div>
					{*$datatable->view()*}
					{$body->view()}
					{if isset($debug)}
						<h2 id="mysql_debug_header"><a id="mysql_debug_showhide" href="#mysql_debug_header" onclick="$('#mysql_debug').toggle();">Debug Tonen/Verstoppen</a></h2>
						<div id="mysql_debug" style="display: none">{$debug}</div>
					{/if}
					{include file='MVC/layout/ubbhulp.tpl'}
					{if isset($minion)}
						{$minion}
					{/if}
				</td>
			</tr>
		</table>

	</body>
</html>