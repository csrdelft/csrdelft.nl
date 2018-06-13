<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index, follow" />
<meta name="author" content="PubCie C.S.R. Delft" />
<meta name="description" content="{{ CsrDelft\model\InstellingenModel::get('stek', 'beschrijving') }}">
<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
<meta name="apple-itunes-app" content="app-id=1112148892, app-argument={{ url()->current() }}">
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:title" content="C.S.R. Delft | @yield('title')" />
<meta property="og:locale" content="nl_nl" />
<meta property="og:image" content="{{ asset('images/beeldmerk.png') }}" />
<meta property="og:description" content="{{ CsrDelft\model\InstellingenModel::get('stek', 'beschrijving') }}" />
<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="{{ url('/forum/rss.xml') }}" />
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
