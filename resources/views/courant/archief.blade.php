@extends('layout')

@section('titel', 'Archief C.S.R.-courant')

@section('breadcrumbs')
	<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>
	<li class="breadcrumb-item"><a href="/courant">Courant</a></li>
	<li class="breadcrumb-item">Archief C.S.R.-courant</li>
	</ol>
@endsection

@section('content')
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a href="/courant" class="nav-link">Courantinzendingen</a>
		</li>
		<li class="nav-item">
			<a href="/courant/archief" class="nav-link active">Archief</a>
		</li>
	</ul>

	<h1>Archief C.S.R.-courant</h1>
	@php($jaar = 0)
	@foreach ($couranten as $courant)
	@if ($jaar != $courant->getJaar())
	@if ($jaar > 0)
	</div>
	@endif
	@php($jaar = $courant->getJaar())
	<div class="CourantArchiefJaar"><h3>{{$jaar}}</h3>
		@endif
		<a href="/courant/bekijken/{{$courant->id}}">{{strftime('%d %B', strtotime($courant->verzendMoment))}}</a><br/>
		@endforeach
	</div>
@endsection
