<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
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
		{foreach from=$scripts item=script}
			<script type="text/javascript" src="{$script}"></script>
		{/foreach}
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-19828019-4']);
			_gaq.push(['_trackPageview']);
			(function() {
				var ga = document.createElement('script');
				ga.type = 'text/javascript';
				ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</body>
</html>