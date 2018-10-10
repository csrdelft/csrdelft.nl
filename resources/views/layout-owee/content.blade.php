@extends('layout-owee.layout')

@section('titel', $titel)

@section('content')
	@if($showmenu)
		@include('layout-owee.menu')
	@endif
	@php($body->view())
@endsection
