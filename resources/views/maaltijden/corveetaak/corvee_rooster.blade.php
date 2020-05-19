<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeTaak[] $taken
 */
?>
@extends('maaltijden.base')

@section('titel', 'Corveerooster')

@section('content')
	@parent
	@if($toonverleden)
		<div class="float-right">
			<a href="/corvee/rooster/verleden" title="Taken in het verleden tonen" class="btn">@icon("time") Toon verleden</a>
		</div>
	@endif
	<table id="maalcie-tabel" class="maalcie-tabel">
		<thead>
		<tr>
			<th>Week</th>
			<th>Datum</th>
			<th>Functie</th>
			@if(!isset($mijn))
				<th colspan="2">Corveeër(s)</th>
			@endif
		</tr>
		</thead>
		<tbody>
		@php($firstOfWeek = false)
		@php($firstOfDatum = false)
		@php($weekColor = false)
		@foreach($rooster as $week => $datums)
			@foreach($datums as $datum => $functies)
				@if($loop->first)
					@php($firstOfWeek = !$firstOfWeek)
				@endif
				@foreach($functies as $taken)
					@if($loop->first)
						@php($firstOfDatum = !$firstOfDatum)
					@endif
					<tr>
						@if($firstOfWeek == true)
							@php($firstOfWeek = !$firstOfWeek)
							@php($weekColor = !$weekColor)
							{{--		{{cycle name="weekColor" assign="weekColor" values="rowColor0,rowColor1"}}--}}
							<td rowspan="{{$loop->count}}"
									@if(!isset($mijn)) class="{{$weekColor ? 'rowColor1': 'rowColor0'}}" @endif >
								<nobr>{{strftime("%W", $datum)}}</nobr>
							</td>
						@elseif($firstOfDatum == 'true')
							<td rowspan="{{$loop->count}}"
									@if(!isset($mijn)) class="{{$weekColor ? 'rowColor1': 'rowColor0'}}" @endif ></td>
						@endif
						@if($firstOfDatum == true)
							@php($firstOfDatum = !$firstOfDatum)
							{{--		{{cycle name="datumColor" assign="datumColor" values="rowColor0,rowColor1"}}--}}
							<td rowspan="{{$loop->count}}"
									@if(!isset($mijn)) class="@cycle('rowColor0', 'rowColor1')" @endif >
								<nobr>{{strftime("%a %e %b", $datum)}}</nobr>
							</td>
						@endif
						@if(array_key_exists(0, $taken))
							<td>
								<nobr>{{$taken[0]->corveeFunctie->naam}}</nobr>
							</td>
						@endif
						@if(!isset($mijn))
							<td>
								@foreach($taken as $taak)
									@if($taak->uid)
										@if($taak->uid === \CsrDelft\service\security\LoginService::getUid())
											@php($class = "taak-self")
										@else
											@php($class= "")
										@endif
									@else
										@php($class= "taak-grijs")
									@endif
									<div class="taak {{$class}}">
										@if($taak->uid)
											@if($taak->uid === \CsrDelft\service\security\LoginService::getUid())
											@endif
											{!! \CsrDelft\repository\ProfielRepository::getLink($taak->uid,instelling('corvee', 'weergave_ledennamen_corveerooster')) !!}
										@else
											<span class="cursief">vacature</span>
										@endif
									</div>
								@endforeach
							</td>
						@endif
					</tr>
				@endforeach
			@endforeach
		@endforeach
		</tbody>
	</table>
@endsection
