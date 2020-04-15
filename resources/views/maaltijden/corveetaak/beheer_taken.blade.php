@extends('maaltijden.base')

@section('titel')
	@if ($maaltijd !== null)
		Maaltijdcorveebeheer: {{$maaltijd->getTitel()}}
	@elseif ($prullenbak)
		Beheer corveetaken in prullenbak
	@else
		Corveebeheer
	@endif
@endsection

@section('content')
	@parent
	@if($prullenbak)
		<p>Op deze pagina kunt u de corveetaken herstellen of definitief verwijderen. Onderstaande tabel toont alle
			corveetaken die in de prullenbak zitten.</p>
		<br/>
	@else
		<p>Op deze pagina kunt u de corveetaken aanmaken, wijzigen en verwijderen
			@if(!empty($maaltijd))  voor de
			@if($maaltijd->archief !== null)
				<span class="dikgedrukt">gearchiveerde</span>
			@elseif($maaltijd->verwijderd)
				<span class="dikgedrukt">verwijderde</span>
			@endif
			maaltijd:<br/>
			@if($maaltijd->archief !== null)
				@icon("compress", null, "Maaltijd is gearchiveerd")
			@elseif($maaltijd->verwijderd)
				@icon("bin", null, "Maaltijd is verwijderd")
			@else
				<a href="/maaltijdenbeheer/beheer/{{$maaltijd->maaltijd_id}}" title="Wijzig gekoppelde maaltijd"
					 class="btn popup">@icon("cup_edit")</a>
			@endif
			<span
				class="dikgedrukt">{{$maaltijd->getTitel()}} op {{$maaltijd->datum->format(LONG_DATE_FORMAT)}} om {{$maaltijd->tijd->format(TIME_FORMAT)}}</span>
		</p>
		@if($maaltijd->verwijderd)
			<p>Onderstaande tabel toont de corveetaken voor deze maaltijd, ook die verwijderd zijn.
		@else
			<p>Onderstaande tabel toont <span class="cursief">alleen</span> de corveetaken voor deze maaltijd die <span
					class="cursief">niet verwijderd</span> zijn.
				@endif
				@else .
				Onderstaande tabel toont alle corveetaken die niet verwijderd zijn.
				@endif
				Taken in het verleden waarvoor wel iemand is ingedeeld maar geen punten zijn toegekend worden geel gemarkeerd.
			</p>
			<p>N.B. U kunt ingedeelde corveeërs eenvoudig ruilen door het icoontje voor de naam te verslepen.</p>
			<br/>
			{{--
			<a href="/corvee/beheer/indelen" title="Leden automatisch indelen voor taken" class="btn">@icon("date") Automatisch indelen</a>
			<a href="/corvee/beheer/herinneren" title="Verstuur herinneringen" class="btn">@icon("clock") Herinneringen versturen</a>
			--}}
			<div class="float-right">
				@if(empty($maaltijd) OR !$maaltijd->verwijderd)
					<a class="btn" onclick="$(this).hide(); window.maalcie.takenShowOld();">@icon("eye") Toon verleden</a>
					<a href="/corvee/beheer/prullenbak" class="btn">@icon("bin_closed") Open prullenbak</a>
					<a @if(!empty($maaltijd))
						 href="/corvee/beheer/nieuw/{{$maaltijd->maaltijd_id}}"
						 @else
						 href="/corvee/beheer/nieuw"
						 @endif
						 class="btn post popup">@icon("add")
						Nieuwe taak</a>
				@endif
			</div>
		@endif
		@if(!empty($repetities) and (empty($maaltijd) or !$maaltijd->verwijderd))
			<form @if(!empty($maaltijd))
						action="/corvee/beheer/nieuw/{{$maaltijd->maaltijd_id}}"
						@else
						action="/corvee/beheer/nieuw"
						@endif
						method="post"
						class="Formulier ModalForm SubmitReset">
				@php(printCsrfField())
				<label for="crid" style="width: auto;">@icon("calendar_add") Periodieke taken aanmaken:</label>&nbsp;
				<select id="crid" name="crv_repetitie_id" value="kies" origvalue="kies" class="FormElement SubmitChange">
					<option selected="selected">kies</option>
					@foreach($repetities as $repetitie)
						<option value="{{$repetitie->crv_repetitie_id}}">{{$repetitie->getCorveeFunctie()->naam}}
							op {{$repetitie->getDagVanDeWeekText()}}</option>
					@endforeach
				</select>
				<a href="/corvee/repetities" class="btn" title="Periodiek corvee beheren">@icon("calendar_edit")</a>
			</form>
		@endif
		<br/>
		<table id="maalcie-tabel" class="maalcie-tabel">
			@foreach($taken as $datum => $perdatum)
				@if($loop->first)
					<thead>
					@include('maaltijden.corveetaak.beheer_taak_head', ['prullenbak' => $prullenbak, 'show' => true, 'datum' => 'first'])
					</thead>
					<tbody></tbody>
				@endif
				@if(!$prullenbak and empty($maaltijd))
					<thead>
					@include('maaltijden.corveetaak.beheer_taak_datum', ['perdatum' => $perdatum, 'datum' => $datum, 'show' => $show])
					</thead>
					<tbody>
					@endif
					@foreach($perdatum as $fid => $perfunctie)
						@foreach($perfunctie as $taak)
							@include('maaltijden.corveetaak.beheer_taak_lijst', ['taak' => $taak, 'show' => $show, 'prullenbak' => $prullenbak])
						@endforeach
					@endforeach
					@if(!$prullenbak and empty($maaltijd))
					</tbody>
				@endif
			@endforeach
		</table>
@endsection
