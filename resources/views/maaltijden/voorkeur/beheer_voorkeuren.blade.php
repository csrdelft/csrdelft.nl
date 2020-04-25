<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeRepetitie $repetitie
 */
?>
@extends('maaltijden.base')

@section('titel', 'Beheer voorkeuren')

@section('content')
	@parent
	<p>
		Op deze pagina kunt u voor alle leden de voorkeuren beheren.
	</p>
	<table id="maalcie-tabel" class="maalcie-tabel">
		@foreach($matrix as $voorkeuren)
		@if($loop->index % 25 === 0)
		@if(!$loop->first)</tbody>@endif
		<thead>
		<tr>
			<th class="text-bottom">Lid</th>
			@foreach($repetities as $repetitie)
				<th class="@cycle('rowColor0','rowColor1')" style="width: 30px;">
					<div style="width: 28px;">
						<a href="/corvee/repetities/{{$repetitie->crv_repetitie_id}}" title="Wijzig corveerepetitie"
							 class="btn popup">
							@icon("calendar_edit")
						</a>
					</div>
					<div style="width: 26px; height: 160px;">
						<div class="vertical niet-dik" style="position: relative; top: 130px;">
							<nobr>{{$repetitie->corveeFunctie->naam}}</nobr>
							<br/>
							<nobr>op {{$repetitie->getDagVanDeWeekText()}}</nobr>
						</div>
					</div>
				</th>
			@endforeach
		</tr>
		</thead>
		<tbody>
		@endif
		@include('maaltijden.voorkeur.beheer_voorkeur_lijst', ['voorkeuren' => $voorkeuren])
		@endforeach
		</tbody>
	</table>
@endsection
