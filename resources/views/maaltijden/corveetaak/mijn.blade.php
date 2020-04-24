<?php
/**
 * @var \CsrDelft\entity\corvee\CorveePuntenOverzicht $punten
 * @var \CsrDelft\entity\corvee\CorveeVrijstelling $vrijstelling
 * @var \CsrDelft\entity\corvee\CorveeTaak[] $taak
 */
?>
@extends('maaltijden.base')

@section('titel', 'Mijn corveeoverzicht')

@section('content')
	@parent
	<p>Deze pagina toont een overzicht van uw aankomende corveetaken, corveepunten en vrijstellingen.
		Voor vragen kunt u contact opnemen met de CorveeCaesar: <a href="mailto:corvee@csrdelft.nl">corvee@csrdelft.nl</a>.
	</p>
	<h3>Corveerooster</h3>
	@if(empty($rooster))
		<p>U bent nog niet ingedeeld.</p>
	@else
		<p>
			De onderstaande tabel toont de aankomende corveetaken waarvoor u bent ingedeeld.
			Als u niet kunt op de betreffende datum bent u zelf verantwoordelijk voor het regelen van een vervanger en dit te
			melden aan de <a href="mailto:corvee@csrdelft.nl">CorveeCaesar</a>.
		</p>
		<p>
			Tip: zoek in het <a href="/corvee/rooster" title="Corveerooster">corveerooster</a> iemand met dezelfde taak
			wanneer
			u zelf wel kunt om te ruilen.
		</p>


		<table class="table table-sm table-striped">
			<thead>
			<tr>
				<th>Week</th>
				<th>Datum</th>
				<th>Functie</th>
			</tr>
			</thead>
			<tbody>
			@foreach($rooster as $week => $datums)
				@foreach($datums as $datum => $taken)
					@foreach($taken as $taak)
						<tr>
							<td>
								{{strftime("%W", $datum)}}
							</td>
							<td>
								<nobr>{{strftime("%a %e %b", $datum)}}</nobr>
							</td>
							@if(array_key_exists(0, $taak))
								<td>
									<nobr>{{$taak[0]->getCorveeFunctie()->naam}}</nobr>
								</td>
							@endif
						</tr>
					@endforeach
				@endforeach
			@endforeach
			</tbody>
		</table>
	@endif

	<h3>Corveepunten</h3>
	<p>
		In de onderstaande tabel is een overzicht te vinden van de punten die u per corveefunctie heeft verdiend met
		daarachter uw bonus/malus-punten indien van toepassing.
		Tussen haakjes staat het aantal keer dat u bent ingedeeld in deze functie.
		Het totaal is uw huidige aantal toegekende corveepunten.
		De prognose geeft aan hoeveel punten u naar verwachting totaal zal hebben aan het einde van het corveejaar.
	</p>
	<table class="table table-sm table-striped" style="width: 350px;">
		<thead>
		<tr>
			<th>Functie</th>
			<th>Punten</th>
		</tr>
		</thead>
		<tbody>
		@foreach($punten->aantal as $fid => $aantal)
			<tr>
				<td>{{$functies[$fid]->naam}} ({{$aantal}})</td>
				<td>{{$punten->punten[$fid]}}
					@if($punten->bonus[$fid] > 0)
						+
					@endif
					@if($punten->bonus[$fid] !== 0)
						{{$punten->bonus[$fid]}}
					@endif
				</td>
			</tr>
		@endforeach
		<tr class="dikgedrukt">
			<td>Totaal</td>
			<td>{{$punten->puntenTotaal}}
				@if($punten->bonusTotaal > 0)
					+
				@endif
				@if($punten->bonusTotaal !== 0)
					{{$punten->bonusTotaal}}
				@endif
			</td>
		</tr>
		<tr class="dikgedrukt">
			<td>Prognose</td>
			<td>{{$punten->prognose}}</td>
		</tr>
		<tr class="dikgedrukt">
			<td>Tekort</td>
			<td style="background-color: {{'#' . $punten->tekortColor}};">{{$punten->tekort}}</td>
		</tr>
		</tbody>
	</table>

	<h3>Corveevrijstelling</h3>
	@if($vrijstelling === false)
		<p>U heeft geen vrijstelling.</p>
	@else
		<p>
			In de onderstaande tabel staat de vrijstelling die u heeft gekregen.
		</p>
		<table class="maalcie-tabel" style="width: 650px;">
			<thead>
			<tr>
				<th>Van</th>
				<th>Tot</th>
				<th>Percentage</th>
				<th>Punten</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>{{strftime("%e %b %Y", strtotime($vrijstelling->begin_datum))}}</td>
				<td>{{strftime("%e %b %Y", strtotime($vrijstelling->eind_datum))}}</td>
				<td>{{$vrijstelling->percentage}}%</td>
				<td>{{$vrijstelling->getPunten()}}</td>
			</tr>
			</tbody>
		</table>
	@endif
@endsection
