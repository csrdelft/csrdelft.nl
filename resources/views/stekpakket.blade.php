@extends('layout')

@section('titel', 'StekPakket kiezen')

@push('styles')
	@stylesheet('app.css')
@endpush

@section('content')
	<StekPakket
		class="vue-context"
		basispakket="{{$basispakket}}"
		:basispakketten="Object.values({{$basispakketten}})"
		:opties="Object.values({{$opties}})"
		opslaan="{{$opslaan}}">
	</StekPakket>
@endsection
