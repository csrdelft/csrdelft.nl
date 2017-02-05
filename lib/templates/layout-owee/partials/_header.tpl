<!DOCTYPE html>
<html>

<head>
    <meta name="description" content="{Instellingen::get('stek', 'beschrijving')}">
    <meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE"/>
    <meta property="og:url" content="{$smarty.const.CSR_ROOT}{$REQUEST_URI}"/>
    <meta property="og:title" content="C.S.R. Delft | {$titel}"/>
    <meta property="og:locale" content="nl_nl"/>
    <meta property="og:image" content="{$smarty.const.CSR_ROOT}/assets/layout/plaetjes/beeldmerk.png"/>
    <meta property="og:description" content="{Instellingen::get('stek', 'beschrijving')}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C.S.R. Delft - {$titel}</title>
    <link rel="shortcut icon" href="{$smarty.const.CSR_ROOT}/assets/layout/plaetjes/favicon.ico"/>
    <link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml"
          href="{$smarty.const.CSR_ROOT}/forum/rss.xml"/>
    {foreach from=$stylesheets item=sheet}
        <link rel="stylesheet" href="{$sheet}" type="text/css"/>
    {/foreach}
    <link rel="stylesheet" type="text/css"
          href="https://fonts.googleapis.com/css?family=Raleway:200,700|Source+Sans+Pro:300,600,300italic,600italic"/>
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="/assets/layout-owee/css/ie9.css"/><![endif]-->
    {foreach from=$scripts item=script}
        <script type="text/javascript" src="{$script}"></script>
    {/foreach}
</head>

<body class="is-loading">
<!-- Page Wrapper -->
<div id="page-wrapper">

    <!-- Header -->
    <header id="header" class="alt">
        <h1><a href="/">C.S.R. Delft</a></h1>
        <nav>
            <a class="inloggen" href="#login">Inloggen</a>
            <a href="#menu">Menu</a>
        </nav>
    </header>

    <!-- Loginform -->
    <nav id="login">
        <a href="#_" class="overlay"></a>
        <div class="inner">
            <h2>Inloggen</h2>
            {$loginform->view()}
            <a href="#_" class="close">Close</a>
        </div>
    </nav>

    <!-- Menu -->
    <nav id="menu">
        <a href="#_" class="overlay"></a>
        <div class="inner">
            <h2>Menu</h2>
            <ul class="links">
                <li><a href="/">Begin</a></li>
                <li><a href="/vereniging">Informatie over C.S.R.</a></li>
                <li><a href="/fotoalbum">Fotoalbum</a></li>
                <li><a href="/forum">Forum</a></li>
                <li><a href="/forum/deel/12">Kamers zoeken/aanbieden</a></li>
                <li><a href="/contact">Contactinformatie</a></li>
                <li><a href="/contact/sponsoring">Bedrijven</a></li>
            </ul>
            <a href="#_" class="close">Close</a>
        </div>
    </nav>
