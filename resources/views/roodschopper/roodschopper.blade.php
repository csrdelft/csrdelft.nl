@extends('layout')

@section('titel', 'Roodschopper')

@section('content')
	@if($verzenden)
		<h1>Emails verzonden naar {{count($saldi)}} leden.</h1>
	@else
		@php($form->view())

		<h2>Doelgroep</h2>
		<p>De volgende {{count($saldi)}} personen zouden een mail krijgen onder de huidige selectie.</p>
		<table class="table">
			<thead>
			<tr>
				<th>Naam</th>
				<th>Uid</th>
				<th>Saldo</th>
			</tr>
			</thead>
			<tbody>
			@foreach($saldi as $saldo)
				<tr>
					<td>{!! $saldo->uid ? \CsrDelft\repository\ProfielRepository::getLink($saldo->uid) : "" !!}</td>
					<td>{{$saldo->uid}}</td>
					<td>{{format_bedrag($saldo->saldo)}}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	@endif
@endsection
