@extends('layout')

@section('titel', $content->getTitel())

@section('breadcrumbs', $content->getBreadCrumbs())

@section('content')
	{!! $content->view() !!}
@endsection
