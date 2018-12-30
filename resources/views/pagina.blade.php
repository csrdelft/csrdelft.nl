@extends('layout')

@section('breadcrumbs', $breadcrumbs)
@section('titel', $titel)
@section('content')
	@php($body->view())
@endsection
