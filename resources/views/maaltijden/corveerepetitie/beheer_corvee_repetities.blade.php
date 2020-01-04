@extends('maaltijden.base')

@if(isset($maaltijdrepetitie))
	@section('titel', 'Corveebeheer maaltijdrepetitie: ' . $maaltijdrepetitie->standaard_titel)
@else
	@section('titel', 'Beheer corveerepetities')
@endif

@section('content')
	@parent
<p>
	Op deze pagina kunt u corveerepetities aanmaken, wijzigen en verwijderen
	@if(isset($maaltijdrepetitie))
		behorend bij de maaltijdrepetitie:<br/>
		<span class="dikgedrukt">{{$maaltijdrepetitie->standaard_titel}}</span>
		<a href="/maaltijdenrepetities/beheer/{{$maaltijdrepetitie->mlt_repetitie_id}}" title="Wijzig gekoppelde maaltijd"
			 class="btn popup">@icon("calendar_edit")</a>
	@else
		.
	@endif
</p>
<p>
	Onderstaande tabel toont
	@if(isset($maaltijdrepetitie))
		<span class="cursief">alleen</span> de corveerepetities voor deze maaltijdrepetitie
	@else
		alle repetities in het systeem
	@endif
	.
</p>
<h3>Repetities verwijderen</h3>
<p>
	Voordat een corveerepetitie verwijderd kan worden moeten eerst alle bijbehorende corveetaken definitief zijn
	verwijderd.
	Dit is dus inclusief maaltijdcorveetaken (die door een gekoppelde maaltijdrepetitie zijn aangemaakt).
	Bij het verwijderen van een gekoppelde maaltijdrepetitie blijven de eventuele gekoppelde corveerepetities bestaan.
</p>
<p>
	N.B. Als u kiest voor "Alles bijwerken" worden alle corveetaken die behoren tot de betreffende corveerepetitie
	bijgewerkt, ongeacht of ze tot een maaltijd behoren. Er worden ook extra taken aangemaakt tot aan het standaard
	aantal.
</p>
<div class="float-right">
	@if(empty($maaltijdrepetitie))
		<a href="/corvee/repetities/nieuw" class="btn post popup">@icon("add") Nieuwe repetitie</a>
	@else
		<a href="/corvee/repetities/nieuw/{{$maaltijdrepetitie->mlt_repetitie_id}}" class="btn post popup">@icon("add")
			Nieuwe repetitie</a>
	@endif
</div>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
	<tr>
		<th>Wijzig</th>
		<th>Functie</th>
		<th>Dag</th>
		<th>Periode</th>
		<th>@icon("tick", null, "Voorkeurbaar")</th>
		<th>Standaard<br/>punten</th>
		<th>Aantal<br/>corveeërs</th>
		<th title="Definitief verwijderen" class="text-center">@icon("cross")</th>
	</tr>
	</thead>
	<tbody>
	@foreach($repetities as $repetitie)
		@include('maaltijden.corveerepetitie.beheer_corvee_repetitie_lijst', ['repetitie' => $repetitie, 'maaltijdrepetitie' => $maaltijdrepetitie])
	@endforeach
	</tbody>
</table>
	@endsection
