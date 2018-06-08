<!DOCTYPE html>
<html>
<head>
    @include('partial.meta')
    <title>C.S.R. Delft - @yield('title', 'Vereniging van Christenstudenten')</title>
    <link href="{{ asset('css/general.css') }}" rel="stylesheet" type="text/css"/>
    @yield('head')
    <script src="{{ asset('js/manifest.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
    @include('partial.analytics')
</head>
<body class="nav-is-fixed">
<header class="cd-main-header">
    <ul class="cd-header-buttons">
        <li><a class="cd-search-trigger" href="#cd-search">Zoeken<span></span></a></li>
        <li><a class="cd-nav-trigger" href="#cd-primary-nav">Menu<span></span></a></li>
    </ul>
</header>
<main class="cd-main-content">
    <div id="cd-zijbalk">
    <a href="/">
        <div class="cd-beeldmerk"></div>
    </a>
        @foreach(\CsrDelft\view\Zijbalk::addStandaardZijbalk([]) as $blok)
            <div class="blok">{!! $blok->view() !!}</div>
        @endforeach
    </div>
    <nav class="cd-page-top">
        <div class="breadcrumbs">@yield('breadcrumbs', Breadcrumbs::render('home'))</div>
    </nav>
    <div class="cd-page-content">
        @yield('content')
    </div>
</main>
@php
    (new \CsrDelft\view\menu\MainMenuView)->view()
@endphp
<div id="cd-main-overlay">
</div>
@if (isset($modal))
<div id="modal-background" style="display: block;"></div>
@php
    $modal->view()
@endphp
@endif
<div id="modal-wrapper" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{--<div class="modal-header">--}}
                {{--<h5 class="modal-title">Modal title</h5>--}}
                {{--<button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
                    {{--<span aria-hidden="true">&times;</span>--}}
                {{--</button>--}}
            {{--</div>--}}
            <div id="modal" class="modal-body">
                <p>Modal body text goes here.</p>
            </div>
            {{--<div class="modal-footer">--}}
                {{--<button type="button" class="btn btn-primary">Save changes</button>--}}
                {{--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>--}}
            {{--</div>--}}
        </div>
    </div>
</div>
</body>
</html>
