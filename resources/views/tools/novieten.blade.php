@extends('layout')

@section('titel', 'Novieten')

@section('content')
	<table class="table">
		<tr>
			<th>UID</th>
			<th>Voornaam</th>
			<th>Tussenvoegsel</th>
			<th>Achternaam</th>
			<th>Mobiel</th>
			<th>Studie</th>
			<th>Nanoviet</th>
		</tr>
		@foreach($novieten as $noviet)
			<tr>
				<td><a href="/profiel/{{$noviet['uid']}}">{{$noviet['uid']}}</a></td>
				<td>{{$noviet['voornaam']}}</td>
				<td>{{$noviet['tussenvoegsel']}}</td>
				<td>{{$noviet['achternaam']}}</td>
				<td>{{$noviet['mobiel']}}</td>
				<td>{{$noviet['studie']}}</td>
				<td>@if($noviet['novietSoort'] == '1') @icon('tick') @else @icon('cross') @endif </td>
			</tr>
		@endforeach
	</table>
@endsection
