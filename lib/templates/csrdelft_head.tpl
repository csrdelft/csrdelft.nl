<meta charset="utf-8">
<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index, follow" />
<meta name="author" content="PubCie C.S.R. Delft" />
<meta name="description" content="{Instellingen::get('stek', 'beschrijving')}">
<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
<meta property="og:url" content="http://csrdelft.nl{$REQUEST_URI}" />
<meta property="og:title" content="C.S.R. Delft | {$titel}" />
<meta property="og:locale" content="nl_nl" />
<meta property="og:image" content="{$CSR_PICS}/layout/beeldmerk.png" />
<meta property="og:description" content="{Instellingen::get('stek', 'beschrijving')}" />
<title>C.S.R. Delft - {$titel}</title>
<link rel="shortcut icon" href="{$CSR_PICS}/layout/favicon.ico" />
<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.xml" />
{foreach from=$stylesheets item=sheet}
	<link rel="stylesheet" href="{$sheet}" type="text/css" />
{/foreach}