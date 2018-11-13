@extends('layout')

@section('titel', $titel)

@section('content')
	{!! $content->view() !!}
@endsection
