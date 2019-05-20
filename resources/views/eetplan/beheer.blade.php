@extends('eetplan.template')

@section('titel', 'Eetplanbeheer')

@section('breadcrumbs')
	@parent
	» <span>Beheer</span>
@endsection

@section('content')
	<h1>Eetplanbeheer</h1>

	@php($huizentable->view())
	@php($bekendentable->view())
	@php($bekendehuizentable->view())

	<a href="/eetplan/nieuw" class="btn btn-primary post popup">Nieuw Eetplan</a>
	<a href="/eetplan/verwijderen" class="btn btn-primary post popup">Eetplan verwijderen</a>

	@include('eetplan.table')
@endsection
