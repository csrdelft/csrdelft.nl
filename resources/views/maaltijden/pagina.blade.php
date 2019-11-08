@extends('maaltijden.base')

@section('titel', $titel)

@section('content')
	@parent

	@php($content->view())
@endsection
