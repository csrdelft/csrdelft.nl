@extends('layout-owee.layout')

@section('titel', $titel)

@section('styles')
	<link rel="stylesheet" href="{{asset("/dist/css/extern.css")}}" type="text/css"/>
	<link rel="stylesheet" href="{{asset("/dist/css/extern-fotoalbum.css")}}" type="text/css"/>
@endsection

@section('content')
	@if($showmenu)
		@include('layout-owee.menu')
	@endif
	@php($body->view())
@endsection

