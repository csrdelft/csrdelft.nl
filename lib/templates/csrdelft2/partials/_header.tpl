<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>C.S.R. Delft - {$body->getTitel()}</title>

    {foreach from=$this->getStylesheets() item=sheet}
        <link rel="stylesheet" href="{$sheet.naam}?{$sheet.datum}" type="text/css" />
    {/foreach}

    <link rel="shortcut icon" href="http://plaetjes.csrdelft.nl/layout/favicon.ico">

    {foreach from=$this->getScripts() item=script}
        <script type="text/javascript" src="{$script.naam}?{$script.datum}"></script>
    {/foreach}

    {literal}
    <script>
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-19828019-4']);
        _gaq.push(['_trackPageview']);
        (function () {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
	</script>
    {/literal}

    <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>

<body>