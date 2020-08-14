@extends('layout')

@section('titel', 'Inschrijflink genereren')

@section('content')
	@if($link)
		<blockquote>
			<h3>Link</h3>
			<p>Stuur deze link naar de rups: <a href="{{$link}}" target="_blank">{{$link}}</a></p>
		</blockquote>
	@endif
	<h3>Genereer inschrijflink</h3>
	<p>Vul de velden in om een inschrijflink met vooringevulde velden te genereren. Alleen de voornaam is verplicht.</p>
	@php($form->view())
@endsection
