@extends('layout-extern.layout')

@section('titel', 'Login')

@section('styles')
	@stylesheet('extern.css')
@endsection

{{--Verberg login popup--}}
@section('loginpopup')
@endsection

@section('loginbutton')
@endsection

@section('content')

	<div>
		<h1>Inloggen</h1>
		<p>U dient in te loggen om verder te gaan</p>
		@php($loginForm->view())
	</div>

@endsection
