@extends('layouts.extern')

@section('content')
    <!-- Banner -->
    <section id="banner-small">
        <div class="inner">
            <a href="/"><img src="/images/logo-next-level.svg" height="140"></a>
        </div>
    </section>

    <!-- Wrapper -->
    <section id="wrapper">
        <section class="wrapper detail kleur1">
            <div class="inner">
                <div class="content">
                    {{--{if isset($menutpl)}{include file="layout-owee/partials/_menu$menutpl.tpl"}{/if}--}}
                    {!! $pagina !!}
                </div>
            </div>
        </section>
        <section id="footer">
            <div class="inner">
                <ul class="copyright">
                    <li>&copy; {{ date('Y') }} - C.S.R. Delft</li>
                </ul>
            </div>
        </section>
    </section>

@endsection