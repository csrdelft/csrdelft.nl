@extends('layout-extern.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern.css')
@endsection

@section('content')
	@if($showmenu)
		@include('layout-extern.menu')
	@endif
	@php($body->view())
	<h2 class="major" style="margin-top:1.5em;">Interesse Formulier</h2>
	@include('layout-extern.form')
@endsection
