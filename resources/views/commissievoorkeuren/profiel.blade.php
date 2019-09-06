@extends('layout')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
		'/' => 'main',
		'/ledenlijst' => 'Ledenlijst',
		'/commissievoorkeuren' => 'Commissievoorkeuren',
		'' => $profiel->getLink(),
	]) !!}
@endsection

@section('content')
<h1>Voorkeuren van lid</h1>
<p>Naam: {!! $profiel->getLink('volledig') !!}</p>

<table class="commissievoorkeuren">
	@php($opties = [1 => 'nee', 2 => 'misschien', 3 => 'ja'])
	@foreach($commissies as $commissie)
		@php($voorkeur = $voorkeuren[$commissie->id])
		<tr>
			<td>{{$commissie->naam}}</td>
			<td>
				@if($voorkeur === null)
					{{$opties[1]}} @else {{$opties[$voorkeur->voorkeur]}}
				@endif
			</td>
		</tr>
	@endforeach
</table>
<h3>Opmerkingen van lid</h3>
<p>{{$lidOpmerking}}</p>
<h3>Opmerkingen van praeses</h3>
@php($opmerkingForm->view())
	@endsection
