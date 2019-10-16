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
				@php($noviet = $sessie->getNoviet())
				<tr>
					@if($loop->index == 1)
						<th rowspan="{{count($avond)}}" class="table-light">{{$sessie->avond}}</th>
					@endif
					<td>{!! CsrDelft\model\ProfielModel::getLink($noviet->uid, 'civitas') !!}</td>
					<td>{{$noviet->mobiel}}</td>
					<td>{{$noviet->email}}</td>
					<td>{{$noviet->eetwens}}</td>
				</tr>
			@endforeach
		@endforeach
	</table>
@endsection
