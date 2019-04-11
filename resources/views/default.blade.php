@extends('layout')

@section('titel', $content->getTitel())

@section('content')
	{!! $content->view() !!}
@endsection
