{{-- Voor als je niet weet of je bent ingelogd --}}
@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-extern.layout')

@section('styles')
	@stylesheet('extern')
@endsection
@endguest

@section('titel', $content->getTitel())

@section('breadcrumbs', $content->getBreadCrumbs())

@section('content')
	{!! $content->view() !!}
@endsection
