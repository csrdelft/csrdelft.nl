@extends('eetplan.template')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/eetplan' => 'Eetplan',
	'/eetplan/noviet' => $noviet->getLink()
	]) !!}
@endsection

@section('content')
<table class="table table-striped">
	<thead>
	<tr>
		<th scope="col">Avond</th>
		<th scope="col">Huis</th>
	</tr>
	</thead>
	<tbody>
	@foreach($eetplan as $sessie)
		@php($huis = $sessie->getWoonoord())
		<tr class="@cycle('donker','licht')">
			<td>{{$sessie->avond->format("d-m-Y")}}</td>
			<td><a href="/groepen/woonoorden/{{$huis->id}}">{{$huis->naam}}</a></td>
		</tr>
	@endforeach
	</tbody>
</table>
@endsection
