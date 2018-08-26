@extends('layout')

@section('breadcrumbs')
	<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a>
	» <a href="/eetplan">Eetplan</a>
@endsection

@section('content')
	@can('P_ADMIN,commissie:NovCie')
	<a href="/eetplan/beheer" class="btn float-right"><span class="ico wrench"></span> Eetplanbeheer</a>
	@endcan
	<h1>Eetplan</h1>
	<div class="geelblokje">
		<h3>LET OP: </h3>
		<p>Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
			koken op het huis waarbij zij gefaeld hebben.</p>
	</div>

	@include('eetplan.table')
@endsection
