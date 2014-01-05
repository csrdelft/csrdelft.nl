<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>C.S.R. Delft | {$this->getTitel()}</title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft" />
	<meta name="robots" content="index, follow" />
	{foreach from=$this->getStylesheets() item=sheet}
		<link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
	{/foreach}
	{foreach from=$this->getScripts() item=script}
		<script type="text/javascript" src="{$script.naam}?{$script.datum}"></script>
	{/foreach}
	<!--[if lt IE 7.]>
		<script defer type="text/javascript" src="/layout/pngfix.js"></script>
	<![endif]-->
	<script type="text/javascript">{literal}
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-19828019-4']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	{/literal}</script>

	<meta property="og:image" content="http://plaetjes.csrdelft.nl/layout/beeldmerk.jpg" />
	<meta property="og:title" content="C.S.R. Delft | {$this->getTitel()}" />
	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/communicatie/forum/rss.xml" />
	<link rel="shortcut icon" href="{$csr_pics}layout/favicon.ico" />
</head>

<body onload="ShowMenu(menu_active);">
<div id="header">{$mainmenu->view()}</div>
<div id="container">
	<div id="main">
		{if is_array($zijkolom)}
			<div id="mainleft">
				{foreach from=$zijkolom item=block}
					<div class="block"><br />{$block->view()}</div>
				{/foreach}
			</div>
		{/if}
		<div id="mainright"{if $zijkolom===false} style="width: 958px;"{/if}>
			{$body->view()}
		</div>
		<div id="footer">
			Gemaakt door <a href="mailto:pubcie@csrdelft.nl" title="PubCie der C.S.R. Delft">PubCie der C.S.R. Delft</a> | <a href="http://validator.w3.org/check/referrer" title="Valideer">XHTML 1.0</a>
		</div>
	</div>
</div>
{$ubbHulp}
{if isset($minion)}{$minion}{/if}
{if isset($db)}
	<h2 id="mysql_debug_header">
		<a id="mysql_debug_showhide" href="#mysql_debug_header" onclick="return toggleDiv('mysql_debug');">Debug Tonen/Verstoppen</a>
	</h2>
	<div id="mysql_debug" style="display: none">{$this->getDebug()}</div>
{/if}
</body>
</html>