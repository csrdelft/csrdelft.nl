@extends('eetplan.overzicht')

@push('breadcrumbs')
	» <span>Beheer</span>
@endpush

@section('body')
	<h1>Eetplanbeheer</h1>

	@php($huizentable->view())
	@php($bekendentable->view())
	@php($bekendehuizentable->view())

	<a href="/eetplan/nieuw" class="btn btn-primary post popup">Nieuw Eetplan</a>
	<a href="/eetplan/verwijderen" class="btn btn-primary post popup">Eetplan verwijderen</a>

	@include('eetplan.table')
@endsection
