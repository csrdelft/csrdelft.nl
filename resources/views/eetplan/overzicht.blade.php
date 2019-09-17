@extends('eetplan.template')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/eetplan' => 'Eetplan',
	]) !!}
@endsection

@section('content')
	@can(P_ADMIN . ',commissie:NovCie')
	<a href="/eetplan/beheer" class="btn btn-primary float-right"><span class="ico wrench"></span> Eetplanbeheer</a>
	@endcan
	<h1>Eetplan</h1>
	<div class="alert alert-warning">
		<h3>LET OP: </h3>
		Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
			koken op het huis waarbij zij gefaeld hebben.
	</div>

	@include('eetplan.table')
@endsection
