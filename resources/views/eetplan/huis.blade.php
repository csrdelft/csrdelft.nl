@extends('eetplan.template')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/eetplan' => 'Eetplan',
	'/eetplan/huis' => '<a href="/groepen/woonoorden/' . $woonoord->id . '">' . $woonoord->naam . '</a>',
	]) !!}
@endsection

@section('content')
	<table class="table table-striped">
		<thead>
		<tr>
			<th scope="col">Avond</th>
			<th scope="col">&Uuml;bersjaarsch</th>
			<th scope="col">Mobiel</th>
			<th scope="col">E-mail</th>
			<th scope="col">Allergie</th>
		</tr>
		</thead>
		@php($oudeDatum = '')
		@php($row = 0)

		@foreach($eetplan as $avond)
			@foreach($avond as $sessie)
				<tr>
					@if($loop->index == 1)
						<th rowspan="{{count($avond)}}" class="table-light">{{$sessie->avond->format("d-m-Y")}}</th>
					@endif
					<td>{!! $sessie->noviet->getLink('civitas') !!}</td>
					<td>{{$sessie->noviet->mobiel}}</td>
					<td>{{$sessie->noviet->email}}</td>
					<td>{{$sessie->noviet->eetwens}}</td>
				</tr>
			@endforeach
		@endforeach
	</table>
@endsection
