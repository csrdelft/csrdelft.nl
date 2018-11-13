@extends('fiscaat.base')

@section('titel', 'CiviSaldo overzicht')

@section('civisaldocontent')
	<h2>Som van saldi</h2>
	@include('fiscaat.saldisom')
@endsection
