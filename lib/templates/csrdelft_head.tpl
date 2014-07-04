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
	<link rel="shortcut icon" href="{$CSR_PICS}/layout/favicon.ico" />
</head>