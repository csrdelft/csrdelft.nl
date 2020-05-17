@extends('layout-extern.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern.css')
@endsection

@section('content')
	@if($showmenu)
		@include('layout-extern.menu')
	@endif
	<div id="lid-worden-tekst">
		@php($body->view())
	</div>
	<div id="interesse-formulier">
		<h2 class="major">Interesse Formulier</h2>
		@include('layout-extern.form')
	</div>
@endsection
