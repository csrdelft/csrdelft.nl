@extends('layout-owee.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern.css')
@endsection

@section('content')
	@if($showmenu)
		@include('layout-owee.menu')
	@endif
	@php($body->view())
@endsection
