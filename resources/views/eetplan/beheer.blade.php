@extends('eetplan.template')

@section('titel', 'Eetplanbeheer')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/eetplan' => 'Eetplan',
	'/eetplan/beheer' => 'Eetplanbeheer',
	]) !!}
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
