<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="msapplication-tap-highlight" content="no" />
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
<meta name="theme-color" content="#0a3292">
<meta name="robots" content="index, follow" />
<meta name="author" content="PubCie C.S.R. Delft" />
<meta name="description" content="{{instelling('stek', 'beschrijving')}}">
<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
<meta name="apple-itunes-app" content="app-id=1112148892, app-argument={{CSR_ROOT}}{{REQUEST_URI}}">
<meta property="og:url" content="{{CSR_ROOT}}{{REQUEST_URI}}" />
<meta property="og:title" content="C.S.R. Delft | @yield('titel')" />
<meta property="og:locale" content="nl_nl" />
<meta property="og:image" content="{{CSR_ROOT}}/dist/images/beeldmerk.png" />
<meta property="og:description" content="{{instelling('stek', 'beschrijving')}}" />
{!! csrfMetaTag() !!}
<meta property="X-CSR-LOGGEDIN" content="{{mag('P_LOGGED_IN') ? 'true' : 'false'}}" />
<title>C.S.R. Delft - @yield('titel')</title>
<link rel="shortcut icon" href="{{CSR_ROOT}}/images/favicon.ico" />
<link rel="manifest" href="/manifest.json">
<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="{{CSR_ROOT}}/forum/rss.xml" />
@stylesheet('common.css')
@foreach(\CsrDelft\view\CompressedLayout::getUserModules() as $sheet)
<link rel="stylesheet" href="{{asset("$sheet.css")}}" type="text/css" />
@endforeach
@stack('styles')
@script('app.js')
@stack('scripts')
<!-- Google Analytics -->
<script>
	window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
	window.ga('create', 'UA-19828019-4', 'auto');
	window.ga('send', 'pageview');
</script>
<script async src='https://www.google-analytics.com/analytics.js'></script>
<!-- End Google Analytics -->
