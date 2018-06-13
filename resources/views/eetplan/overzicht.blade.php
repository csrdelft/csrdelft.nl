@extends('layouts.intern')

@section('title', 'Eetplan')
@section('breadcrumbs', Breadcrumbs::render('eetplan.overzicht'))

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/eetplan.css')]])
@endsection

@section('content')
    @mag('P_ADMIN,commissie:NovCie')
        <a href="{{ route('eetplan.beheer') }}" class="btn float-right"><span class="ico wrench"></span> Eetplanbeheer</a>
    @endmag
<h1>Eetplan</h1>
<div class="geelblokje">
    <h3>LET OP: </h3>
    <p>Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
        koken op het huis waarbij zij gefaeld hebben.</p>
</div>

@include('eetplan.table')
@endsection