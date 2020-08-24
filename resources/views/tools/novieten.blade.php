<?php
/**
 * @var \CsrDelft\entity\profiel\Profiel[] $novieten
 */
?>
@extends('layout')

@section('titel', 'Novieten')

@section('content')
	<table class="table">
		<tr>
			<th scope="col">UID</th>
			<th scope="col">Voornaam</th>
			<th scope="col">Tussenvoegsel</th>
			<th scope="col">Achternaam</th>
			<th scope="col">Mobiel</th>
			<th scope="col">Studie</th>
			<th scope="col">Nanoviet</th>
			<th scope="col">Matrixplek</th>
		</tr>
		@foreach($novieten as $noviet)
			<tr>
				<td><a href="/profiel/{{$noviet->uid}}">{{$noviet->uid}}</a></td>
				<td>{{$noviet->voornaam}}</td>
				<td>{{$noviet->tussenvoegsel}}</td>
				<td>{{$noviet->achternaam}}</td>
				<td>{{$noviet->mobiel}}</td>
				<td>{{$noviet->studie}}</td>
				<td>@if($noviet->novietSoort == '1') @icon('tick') @else @icon('cross') @endif </td>
				<td>@if($noviet->matrixPlek !== null) {{['Voor', 'Midden', 'Achter'][$noviet->matrixPlek]}} @endif</td>
			</tr>
		@endforeach
	</table>
@endsection
