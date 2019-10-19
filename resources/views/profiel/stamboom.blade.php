<?php /** @var \CsrDelft\model\entity\profiel\Profiel $profiel */ ?>
@extends('layout')

@section('titel')
	Stamboom voor het geslacht van {{$profiel->getNaam()}}
@endsection

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/ledenlijst' => 'Leden',
	'/profiel/' . $profiel->uid => $profiel->getNaam(),
	'' => 'Stamboom',
	]) !!}
@endsection

@section('content')
	<h1>Nageslacht van {{$profiel->getNaam()}} ({{$profiel->getNageslachtGrootte()}})</h1>

	<div class="tree">
		<ul>
			<li>
				@php($patroonNaam = \CsrDelft\model\ProfielModel::getNaam($profiel->patroon))
				<a href="/profiel/{{$profiel->patroon}}/stamboom"
					 title="Bekijk het nageslacht van {{$patroonNaam}}">&uArr; {{$patroonNaam}}</a>
				@include('profiel.stamboom_node', ['profielen' => [$profiel]])
			</li>
		</ul>
	</div>
@endsection
