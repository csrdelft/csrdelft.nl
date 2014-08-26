<!DOCTYPE html>
<html>
	<head>
		{include file='csrdelft_head.tpl'}
	</head>
	<body>
		<div id="header">{$mainmenu->view()}</div>
		<div id="container">
			<div id="main">
				{if is_array($zijkolom)}
					<div id="mainleft"{if LidInstellingen::get('layout', 'fixed') == 'vast'} class="scroll-fix"{/if}>
						{foreach from=$zijkolom item=block}
							<div class="block">{$block->view()}</div>
						{/foreach}
					</div>
				{/if}
				<div id="mainright"{if $zijkolom === false} style="width: 958px;"{*FIXME*}{/if}>
					<div id="popup-background"{if isset($popup)} style="display: block;"{/if}></div>
					<div id="popup" class="outer-shadow dragobject" style="top: {$popuptop}px; left: {$popupleft}px;{if isset($popup)} display: block;{/if}">
						{if isset($popup)}
							{$popup->view()}
						{/if}
					</div>
					{*$datatable->view()*}
					{$body->view()}
				</div>
			</div>
			<div id="footer">
				Gemaakt door <a href="mailto:pubcie@csrdelft.nl" title="PubCie der C.S.R. Delft">PubCie der C.S.R. Delft</a>
			</div>
		</div>
		{include file='MVC/layout/ubbhulp.tpl'}
		{if isset($minion)}
			{$minion}
		{/if}
		{if isset($debug)}
			<h2 id="mysql_debug_header"><a id="mysql_debug_showhide" href="#mysql_debug_header" onclick="$('#mysql_debug').toggle();">Debug Tonen/Verstoppen</a></h2>
			<div id="mysql_debug" style="display: none">{$debug}</div>
		{/if}
	</body>
</html>