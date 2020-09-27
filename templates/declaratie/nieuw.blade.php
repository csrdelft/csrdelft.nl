@extends('layout')

@section('titel', 'Nieuwe declaratie')

@push('styles')
	@stylesheet('app')
@endpush

@section('content')
	<h2>Nieuwe declaratie</h2>
	<Declaratie
		class="vue-context"
		type="nieuw"
		:categorieen="{1: 'OWeeCie', 2: 'DiesCie', 3: 'MaalCie', 4: 'Anders'}">
	</Declaratie>
@endsection
