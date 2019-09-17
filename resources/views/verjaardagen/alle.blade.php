@extends('layout')

@section('titel', 'Verjaardagen')

@section('content')
	<h1>Verjaardagen</h1>
	<div class="row">
		@for($m = 0; $m < 12; $m++)
			@php($maand = ($dezemaand - 1 + $m) % 12 + 1)
			<div class="col-xl-2 col-md-3 col-sm-4 mb-3">
				<table class="table table-sm">
					<tr>
						<th colspan="2"><h3>{{ucfirst(strftime("%B", mktime(0, 0, 0, $maand, 10)))}}</h3></th>
					</tr>
					@foreach($verjaardagen[$maand - 1] as $verjaardag)
						<tr>
							<td class="text-right @if($verjaardag->isJarig()) dikgedrukt cursief @endif ">
								{{date('j', strtotime($verjaardag->gebdatum))}}
							</td>
							<td @if($verjaardag->isJarig()) class="dikgedrukt cursief" @endif >
								&nbsp;
								{!! $verjaardag->getLink('civitas') !!}
							</td>
						</tr>
					@endforeach
				</table>
			</div>
		@endfor
	</div>
@endsection
