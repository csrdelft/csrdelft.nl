@extends('layout')

@section('titel', 'StekPakket kiezen')

@push('styles')
	@stylesheet('app.css')
@endpush

@section('content')
	<StekPakket
		class="vue-context"
		:basispakketten="Object.values({{$basispakketten}})"
		:opties="Object.values({{$opties}})">
	</StekPakket>
@endsection
