@extends('base')

@section('titel', 'Foutmelding')

@section('breadcrumbs', '')

@section('content')
	<h1>400: Er is iets fout gegaan.</h1>
	<p>
		{{ $bericht }}
	</p>
@endsection
