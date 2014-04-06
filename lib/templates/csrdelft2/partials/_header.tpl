<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
	<title>C.S.R. Delft - {$body->getTitel()}</title>

	{foreach from=$view->getStylesheets() item=sheet}
		<link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
	{/foreach}

	<link rel="shortcut icon" href="http://plaetjes.csrdelft.nl/layout/favicon.ico">

	{foreach from=$view->getScripts() item=script}
		<script type="text/javascript" src="{$script.naam}?{$script.datum}"></script>
	{/foreach}

	{literal}
		<script>
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
	{/literal}

	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<meta property="og:url" content="http://csrdelft.nl{Instellingen::get('menu', 'path')}" />
	<meta property="og:title" content="C.S.R. Delft - {$body->getTitel()}" />
	<meta property="og:locale" content="nl_nl" />
	<meta property="og:image" content="http://plaetjes.csrdelft.nl/layout/beeldmerk.jpg" />
</head>

<body>