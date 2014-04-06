<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>C.S.R. Delft | {$body->getTitel()}</title>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
		<meta name="author" content="PubCie C.S.R. Delft" />
		<meta name="robots" content="index, follow" />
		{foreach from=$view->getStylesheets() item=sheet}
			<link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
		{/foreach}
		{foreach from=$view->getScripts() item=script}
			<script type="text/javascript" src="{$script.naam}?{$script.datum}"></script>
		{/foreach}
		<!--[if lt IE 7.]>
			<script defer type="text/javascript" src="/layout/pngfix.js"></script>
		<![endif]-->
		<script type="text/javascript">
			{literal}
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
			{/literal}
		</script>
		<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
		<meta property="og:url" content="http://csrdelft.nl{Instellingen::get('stek', 'request')}" />
		<meta property="og:title" content="C.S.R. Delft | {$body->getTitel()}" />
		<meta property="og:locale" content="nl_nl" />
		<meta property="og:image" content="http://plaetjes.csrdelft.nl/layout/beeldmerk.png" />
		<meta property="og:description" content="{Instellingen::get('thuispagina', 'beschrijving')}" />
		<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.xml" />
		<link rel="shortcut icon" href="{$CSR_PICS}layout/favicon.ico" />
	</head>
	<body>
		<div id="header">{$mainmenu->view()}</div>
		<div id="container">
			<div id="main">
				{if is_array($zijkolom)}
					<div id="mainleft">
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
	</body>
</html>