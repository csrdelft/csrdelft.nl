<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="{CsrDelft\model\InstellingenModel::get('stek', 'beschrijving')}">
    <meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE"/>
    <meta property="og:url" content="{$smarty.const.CSR_ROOT}{$REQUEST_URI}"/>
    <meta property="og:title" content="C.S.R. Delft | {$titel}"/>
    <meta property="og:locale" content="nl_nl"/>
    <meta property="og:image" content="{{ asset('images/beeldmerk.png') }}"/>
    <meta property="og:description" content="{CsrDelft\model\InstellingenModel::get('stek', 'beschrijving')}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>C.S.R. Delft - {{ config('app.name', 'Home') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}"/>
    <link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml"
          href="{$smarty.const.CSR_ROOT}/forum/rss.xml"/>
    <link href="{{ asset('dist/css/extern.css') }}" rel="stylesheet"/>
    <script src="{{ asset('dist/js/manifest.js') }}"></script>
    <script src="{{ asset('dist/js/vendor.js') }}"></script>
    <script src="{{ asset('dist/js/extern.js') }}"></script>

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
        <div class="inner container">
            <h2>Inloggen</h2>
            {!! Form::open(['route' => 'login']) !!}
                <div class="InputField">
                    {!! Form::text('uid', null, ['placeholder' => 'Bijnaam  of lidnummer', 'class' => 'field']) !!}
                </div>
                <div class="InputField">
                    {!! Form::password('password', ['placeholder' => 'Wachtwoord', 'class' => 'field']) !!}
                </div>
                <div class="InputField">
                    {!! Form::checkbox('remember', 1, null, ['id' => 'remember-checkbox', 'class' => 'field']) !!}
                    {!! Form::label('remember-checkbox', 'Blijf ingelogd') !!}
                </div>
                {!! Form::submit('Inloggen') !!}
            {!! Form::close() !!}
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
                <li><a href="/contact/bedrijven">Bedrijven</a></li>
            </ul>
            <a href="#_" class="close">Close</a>
        </div>
    </nav>

    @yield('content')

</div>
<script src="https://www.google.com/recaptcha/api.js?hl=nl" defer></script>
</body>
</html>

