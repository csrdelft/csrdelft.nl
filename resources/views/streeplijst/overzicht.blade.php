<?php
/**
 * @var $streeplijstoverzicht CsrDelft\entity\Streeplijst[]
 */
?>

@extends('layout')

@section('titel', 'Streeplijstgenerator')

@section('content')
	<h2>Streeplijstgenerator</h2>

	<table class="table table-striped">
		<thead>
		<tr>
			<th scope="col">Aanmaakdatum</th>
			<th scope="col">Streeplijst</th>
			<th scope="col">Leden van de streeplijst</th>
			<th scope="col">Inhoud van de streeplijst</th>
			<th scope="col"></th>
		</tr>
		</thead>
		<tbody>
		@forelse($streeplijstoverzicht as $streeplijst)
			<tr>
				<td>{{date_format_intl($streeplijst->aanmaakdatum, DATE_FORMAT)}}</td>
				<td style="font-weight:bold">{{$streeplijst-> naam_streeplijst}}</td>
				<td>{{truncate($streeplijst-> leden_streeplijst,318, '...')}}</td>
				<td>{{$streeplijst-> inhoud_streeplijst}}</td>
				<td>
					<div class="btn-group" role="group" aria-label="Basic example">
						<a href="/streeplijst/genereren/{{$streeplijst->id}}">
						<span type="button"
									class="btn btn-success btn-rounded btn-sm">Genereer</span></a>
						<a href="/streeplijst/bewerken/{{$streeplijst->id}}">
						<span type="button"
									class="btn btn-warning btn-rounded btn-sm">Bewerk</span></a>
						<a href="/streeplijst/verwijderen/{{$streeplijst->id}}">
						<span type="button"
									class="btn btn-danger btn-rounded btn-sm">Verwijder</span> </a>
					</div>
				</td>
			</tr>
		@empty
			<tr>
				<td colspan="5">Er zijn geen streeplijsten.</td>
			</tr>
		@endforelse
		</tbody>
	</table>

	<h4>Maak of bewerk een lijst</h4>
	<p>Een streeplijst heeft dat wat gestreept moet worden in de titelrij en heeft de leden op wie gestreept wordt in de
		eerste kolomrij.</p>
	<h4>Streeplijstselectie</h4>
	<form action="/streeplijst/selectie" method="post">
		{{printCsrfField()}}

		<h5> Leden op de streeplijst</h5>

		<div>
			<em>Verticale:</em>
			<input type="radio" name="verticale" id="alleverticale" value="alle" checked>
			<label for="alleverticale"> Alle </label>
			@foreach($verticalen as $verticale)
				@if($verticale->letter)
					<input type="radio" name="verticale" id="verticale_{{$verticale->letter}}" value="{{$verticale->letter}}">
					<label for="verticale_{{$verticale->letter}}"> {{$verticale->naam}}</label>
				@endif
			@endforeach
		</div>

		<div>
			<em>Lichting:</em>
			<input type="radio" name="lichting" id="alle" value="alle" checked>
			<label for="alle"> Alle </label>
			@foreach(range($jongstelidjaar-8, $jongstelidjaar) as $lichting)
				<input type="radio" name="lichting" id="lichting_{{$lichting}}" value="{{$lichting}}">
				<label for="lichting_{{$lichting}}"> {{$lichting}}</label>
			@endforeach
		</div>

		<div>
			<em>Type leden:</em>
			@foreach(CsrDelft\model\entity\LidStatus::getLidLike() as $lidstatus)
				<input type="checkbox" id="{{$lidstatus}}" name="ledentype[]" value="{{$lidstatus}}" checked>
				<label for="{{$lidstatus}}">{{CsrDelft\model\entity\LidStatus::from($lidstatus)->getDescription()}}</label>

			@endforeach

			@foreach(CsrDelft\model\entity\LidStatus::getOudLidLike() as $lidstatus)
				<input type="checkbox" id="{{$lidstatus}}" name="ledentype[]" value="{{$lidstatus}}">
				<label for="{{$lidstatus}}">{{CsrDelft\model\entity\LidStatus::from($lidstatus)->getDescription()}}</label>

			@endforeach
		</div>

		<h5>Streepbare eenheden op de streeplijst</h5>
		<div><em>Drankassortiment:</em>
			<input type="checkbox" id="Gulpener" name="streepbareUnits[]" value="Gulpener">
			<label for="Gulpener">Gulpener</label>
			<input type="checkbox" id="Fris" name="streepbareUnits[]" value="Fris">
			<label for="Fris">Fris</label>
		</div>

		<div>
			<em>HV-aanwezigheid:</em>
			<input type="checkbox" id=Aanwezig name="streepbareUnits[]" value="Aanwezig">
			<label for="Aanwezig">Aanwezig</label>
			<input type="checkbox" id="Dispensatie" name="streepbareUnits[]" value="Dispensatie">
			<label for="Dispensatie">Dispensatie</label>
			<input type="checkbox" id="Afwezig" name="streepbareUnits[]" value="Afwezig">
			<label for="Afwezig">Afwezig</label>
		</div>

		<h5>Opmaak</h5>
		<div>
			<em>Alfabetisch sorteren: </em>
			<input type="checkbox" id="Namen van leden" name="opmaakabc" value="Namen van leden">
			<label for="Namen van leden">Namen van leden </label>
			<input type="checkbox" id="Inhoud van lijst" name="opmaakInhoud" value="Inhoud van lijst">
			<label for="Inhoud van lijst">Inhoud van lijst</label>
		</div>

		<div>
			<em>Stijl van naamweergave: </em>
			<input type="radio" name="naamopmaak" id="VoorEnAchternaam" value="volledig" checked>
			<label for="VoorEnAchternaam">Voor- en achternaam</label>
			<input type="radio" name="naamopmaak" id="Achternaam" value="streeplijst">
			<label for="Achternaam">Achternaam, voornaam </label>
			<input type="radio" name="naamopmaak" id="Civitas" value="civitas">
			<label for="Civitas">Civitas</label>
		</div>

		<input type="submit" class="btn btn-info btn-rounded btn-xs"
					 name="selecteer" value="Selecteer"/>
	</form>

	<h5>Maak je eigen lijst</h5>
	<form action="/streeplijst/genererenZonderId" method="get" id="streeplijstform">

		<div class="form-group">
			<label for="naam_streeplijst">Naam van de streeplijst:</label>
			<input placeholder="Naam van streeplijst" name="naam_streeplijst" id="naam_streeplijst" type="text"
						 value="{{$huidigestreeplijst->naam_streeplijst}}"
						 class="form-control" aria-describedby="naam_streeplijst">
			<label for="leden_streeplijst">Leden van streeplijst:</label>
			<textarea class="form-control"
								placeholder="Voer hier je streepbare leden in. Scheid namen met een puntkomma. Bijv.: Pietje; Jantje; Puk"
								id="leden_streeplijst" name="leden_streeplijst" rows="3"
								cols="50">{{$huidigestreeplijst->leden_streeplijst}}</textarea>
			<label for="inhoud_streeplijst">Inhoud van streeplijst:</label>
			<textarea class="form-control"
								placeholder="Voer hier je zelfgekozen streepbare goederen/dingen/eenheden in. Scheid dingen met een puntkomma. Bijv.: bier;wijn;wodka"
								id="inhoud_streeplijst" name="inhoud_streeplijst" rows="3"
								cols="50">{{$huidigestreeplijst->inhoud_streeplijst}}</textarea>
		</div>


		<div>
			<input type="button" onclick="this.form.action='/streeplijst/aanmaken'; this.form.submit()"
						 class="btn btn-info btn-rounded btn-xs"
						 name="opslaan" value="Sla lijst op"/>
			<input type="button" onclick="this.form.action='/streeplijst/genererenZonderId'; this.form.submit()"
						 class="btn btn-success btn-rounded btn-xs"
						 name="genereer" value="Genereer lijst"/>
		</div>
	</form>

@endsection

