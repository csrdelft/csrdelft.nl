<?php
/**
 * @var \CsrDelft\entity\courant\CourantBericht[] $berichten
 */
?>
@extends('layout')

@section('titel', 'Inzendingen C.S.R.-courant')

@section('breadcrumbs')
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>

		@if ($form->getModel()->titel)
			<li class="breadcrumb-item"><a href="/courant">Courant</a></li>
			<li class="breadcrumb-item">{{$form->getModel()->titel}}</li>
		@else
			<li class="breadcrumb-item">Courant</li>
		@endif
	</ol>
@endsection

@section('content')
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a href="/courant" class="nav-link active">Courantinzendingen</a>
		</li>
		<li class="nav-item">
			<a href="/courant/archief" class="nav-link">Archief</a>
		</li>
	</ul>

	<h1>C.S.R.-courant</h1>
	<p>
		De C.S.R.-courant wordt elke maandagavond verzonden naar alle leden van C.S.R..
		Als u uw bericht voor 22:00 invoert, kunt u tamelijk zeker zijn van plaatsing in de courant.
		De PubCie streeft ernaar de courant rond 23:00/24:00 bij u in uw postvak te krijgen.
	</p>
	@if(count($berichten) > 0)
		<div id="courantKnoppenContainer">
			@if($magVerzenden)
				<a href="/courant/verzenden" title="De C.S.R.-courant wilt versturen?" class="btn btn-primary post confirm">Verzenden</a>
				<a href="/courant/voorbeeld" class="btn btn-primary" target="_blank">Laat voorbeeld zien</a>
			@endif
		</div>
		{{-- geen overzicht van berichten bij het bewerken... --}}
		<h3>Overzicht van berichten:</h3>
		<dl>
			@foreach($berichten as $bericht)
				<dt>
					<span
						class="onderstreept">{{$bericht->cat ? $bericht->cat->getDescription() : 'Geen categorie'}}</span>
					@if($magBeheren)
						{!! $bericht->schrijver->getLink('civitas') !!}
					@endif
					<span class="dikgedrukt">{{$bericht->titel}}</span>
					@if($bericht->magBeheren())
						<a class="btn btn-primary" href="/courant/bewerken/{{$bericht->id}}">bewerken</a>
						<a class="btn btn-primary post confirm ReloadPage" href="/courant/verwijderen/{{$bericht->id}}"
							 title="Bericht verwijderen">verwijderen</a>
					@endif
				</dt>
				<dd id="courantbericht{$bericht->id}"></dd>
				@if(!$bericht->magBeheren())
					<dd>{!! bbcode($bericht->bericht, "mail") !!}</dd>
				@endif
			@endforeach
		</dl>
	@endif

	@php($form->view())
@endsection
