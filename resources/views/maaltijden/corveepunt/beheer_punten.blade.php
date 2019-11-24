@extends('maaltijden.base')

@section('titel', 'Beheer corveepunten')

@section('content')
	@parent
	<p>
		Op deze pagina kunt u voor alle leden de corveepunten beheren.
	</p>
	<p>
		De onderstaande tabel bevat een overzicht van de punten die per corveefunctie zijn verdiend.
		Achter de komma staat het aantal keer dat het lid is ingedeeld voor de betreffende functie.
		De kolom punten is exclusief bonus/malus.
		De kolom prognose geeft aan hoeveel punten het lid naar verwachting totaal zal hebben aan het einde van het
		corveejaar.
		Een * geeft aan dat het lid een vrijstelling heeft.
	</p>
	<h3>Corveejaar resetten</h3>
	<p>
		Controleer vooraf zelf of alle punten zijn toegekend naar wens.
		Niet toegewenzen punten voor taken in het verleden, waarvoor wel iemand is ingedeeld, worden geel gemarkeerd in het
		overzicht onder beheer taken.
		De reset omvat een hertelling:<br/>
		NieuwPuntentotaal = Corveepunten + bonus + omhoogAfronden(teBehalenCorveepunten * %Vrijstelling) -
		teBehalenCorveepunten<br/>
		En zet vervolgens de bonus/malus weer op nul.
	<p>
		N.B. Alle corveetaken in het verleden worden bij de reset naar de prullenbak verplaatst en alle verlopen
		vrijstellingen worden definitief verwijderd!
	</p>
	<div class="float-right">
		<a href="/corvee/punten/resetjaar" title="Reset corveejaar" class="btn post confirm">@icon("lightning") Corveejaar
			resetten</a>
	</div>
	<table id="maalcie-tabel" class="maalcie-tabel">
		@foreach($matrix as $puntenlijst)
		@if($loop->index % 25 === 0)

		@if(!$loop->first)
		</tbody>
		@endif
		<thead>
		<tr class="text-bottom">
			<th>Lid</th>
			@foreach($functies as $functie)
				<th class="@cycle('rowColor0','rowColor1')" style="padding: 5px;">
					<div style="width: 17px; height: 160px;">
						<div class="vertical niet-dik" style="position: relative; top: 140px;">
							<nobr>{{$functie->naam}}</nobr>
						</div>
					</div>
				</th>
			@endforeach
			<th>Punten</th>
			<th>Bonus<br/>/malus</th>
			<th>Prognose</th>
		</tr>
		</thead>
		<tbody>
		@endif
		@include('maaltijden.corveepunt.beheer_punten_lijst', ['puntenlijst' => $puntenlijst])
		@endforeach
		</tbody>
	</table>
@endsection
