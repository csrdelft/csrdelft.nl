<head>
	<title>C.S.R. Delft | Pauper </title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft" />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" href="/layout/undohtml.css" type="text/css">
	<link rel="stylesheet" href="/layout/ubb.css" type="text/css">
	<link rel="stylesheet" href="/layout/csrdelft.css" type="text/css">
	<link rel="stylesheet" href="/layout/forum.css" type="text/css">
	<script type="text/javascript" src="/layout/js/jquery/jquery-2.1.0.js"></script>
	<script type="text/javascript" src="/layout/js/csrdelft.js"></script>
	<script type="text/javascript" src="/layout/js/dragobject.js"></script>
	<script type="text/javascript" src="/layout/js/forum.js"></script>
	<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="/layout/pngfix.js"></script>
	<![endif]-->
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
	<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE">
	<meta property="og:title" content="C.S.R. Delft | Pauper">
	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.xml">
	<link rel="shortcut icon" href="http://plaetjes.csrdelft.nl/layout/favicon.ico">
</head>
<body>
	<?php
// pauper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)

	require_once 'configuratie.include.php';

	$_SESSION['pauper'] = true;

	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$pagina = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
	$pagina->view();

# Laatste forumberichten
	require_once 'MVC/model/ForumModel.class.php';
	require_once 'MVC/view/ForumView.class.php';
	$forum = new ForumDeelView(ForumDelenModel::instance()->getRecent());
	$forum->view();
	?>
</body>
</html>