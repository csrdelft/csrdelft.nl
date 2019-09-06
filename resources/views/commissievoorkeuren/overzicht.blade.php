@extends('layout')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
		'/' => 'main',
		'/ledenlijst' => 'Ledenlijst',
		'/commissievoorkeuren' => 'Commissievoorkeuren',
	]) !!}
@endsection

@section('content')
	<h2>Commissievoorkeuren</h2>
	<div class="col-md-6">
		<p>Klik op een commissie om de voorkeuren te bekijken</p>
		@foreach($categorien as $cid => $cat)
			<h2>{{$cat["categorie"]->naam}}</h2>
			<ul>
				@foreach($cat['commissies'] as $commissie)
					<li @if(!$commissie->zichtbaar) style="opacity: .50" @endif ><a
							href="/commissievoorkeuren/overzicht/{{$commissie->id}}">{{$commissie->naam}}</a></li>
				@endforeach
				@if(count($cat['commissies']) == 0)
					Deze categorie bevat geen commissies.
					<a href="/commissievoorkeuren/verwijdercategorie/{{$cid}}" class="btn post ReloadPage">Categorie
						verwijderen</a>
				@endif
			</ul>
		@endforeach
	</div>
	<div class="col-md-6">
		@php($categorieFormulier->view())
		@php($commissieFormulier->view())
	</div>
@endsection
