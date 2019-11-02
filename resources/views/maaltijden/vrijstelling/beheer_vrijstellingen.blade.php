@extends('maaltijden.base')

@section('titel', 'Beheer vrijstellingen')

@section('content')
	@parent
	<p>
		Op deze pagina kunt u vrijstellingen aanmaken, wijzigen en verwijderen. Onderstaande tabel toont alle vrijstellingen
		in het systeem.
	</p>
	<p>
		N.B. Pas bij het resetten van het corveejaar worden de punten toegekend (te behalen corveepunten per jaar maal het
		vrijstellingspercentage afgerond naar boven).
	</p>
	<div class="float-right"><a href="/corvee/vrijstellingen/nieuw" class="btn post popup">@icon("add") Nieuwe
			vrijstelling</a></div>
	<table id="maalcie-tabel" class="maalcie-tabel">
		<thead>
		<tr>
			<th>Wijzig</th>
			<th>Lid</th>
			<th>Van @icon("bullet_arrow_up")</th>
			<th>Tot</th>
			<th>Percentage</th>
			<th>Punten</th>
			<th title="Definitief verwijderen" class="text-center">@icon("cross")</th>
		</tr>
		</thead>
		<tbody>
		@foreach($vrijstellingen as $vrijstelling)
			@include('maaltijden.vrijstelling.beheer_vrijstelling_lijst', ['vrijstelling' => $vrijstelling])
		@endforeach
		</tbody>
	</table>
@endsection
