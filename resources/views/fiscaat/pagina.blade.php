@extends('fiscaat.base')

@section('titel', $titel)

@section('civisaldocontent')
	@php($view->view())
@endsection
