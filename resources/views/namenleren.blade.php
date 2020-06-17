@extends('layout')

@section('titel', 'Namen leren')

@push('styles')
	@stylesheet('app')
@endpush

@section('content')
	<h1>Namen leren</h1>
	<p>Namen leren zonder ze ongemakkelijk te vragen op de borrel? Selecteer de lichting, verticale en antwoordmethode en start!</p>

	<NamenLeren class="vue-context" :leden="Object.values({{$leden}})"></NamenLeren>
@endsection
