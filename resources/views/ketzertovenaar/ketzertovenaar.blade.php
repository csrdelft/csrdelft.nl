@extends('layout')

@section('titel', 'KetzerTovenaar')

@push('styles')
	@stylesheet('app.css')
@endpush

@section('content')
	<KetzerTovenaar class="vue-context"></KetzerTovenaar>
@endsection
