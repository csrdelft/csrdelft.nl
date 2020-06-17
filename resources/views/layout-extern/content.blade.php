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
@endsection
