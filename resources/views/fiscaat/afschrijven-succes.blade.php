@extends('fiscaat.base')

@section('titel', 'Bulk afschrijven CiviSaldo - Controle')

@section('civisaldocontent')
	<h2>Succesvol afgeschreven</h2>
	<p>Er zijn {{$aantalSucces}} bestellingen toegevoegd. Vergeet niet <b>{{sprintf('€%.2f', $totaal)}}</b> uit Tussenrekening CiviSaldi - Incidenteel te halen in Exact.</p>
@endsection
