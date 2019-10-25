@extends('layout')

@section('titel', 'Beheer abonnementen')

@section('content')
	<p>
		Op deze pagina kunt u alle abonnementen beheren en zoeken.
	</p>
	<form action="/maaltijden/abonnementen/beheer/novieten" method="post"
				class="Formulier ModalForm SubmitReset float-right">
		@php(printCsrfField())
		Abonneer novieten op:
		<select name="mrid" origvalue="kies" class="FormElement SubmitChange">
			<option selected="selected">kies</option>
			@foreach($aborepetities as $repetitie)
				<option value="{{$repetitie->mlt_repetitie_id}}" class="save">{{$repetitie->standaard_titel}}</option>
			@endforeach
		</select>
	</form>
	<div class="inline" style="width: 30%;"><label for="toon">Toon abonnementen:</label>
	</div><select name="toon" onchange="location.href='/maaltijden/abonnementen/beheer/'+this.value;">
		<option value="waarschuwingen" class="arrow" @if($toon === 'waarschuwing') selected="selected" @endif >
			waarschuwingen
		</option>
		<option value="ingeschakeld" class="arrow" @if($toon === 'in') selected="selected" @endif >ingeschakeld</option>
		<option value="abonneerbaar" class="arrow" @if($toon === 'abo') selected="selected" @endif >abonneerbaar</option>
	</select>
	<p>&nbsp;</p>
	<table id="maalcie-tabel" class="maalcie-tabel">
		@foreach($matrix as $vanuid => $abonnementen)
		@if($loop->index % 25 === 0)
		@if(!$loop->first) </tbody> @endif
		@include('maaltijden.abonnement.beheer_abonnement_head', ['repetities' => $repetities])
		<tbody>
		@endif
		@include('maaltijden.abonnement.beheer_abonnement_lijst', ['vanuid' => $vanuid, 'abonnementen' => $abonnementen])
		@endforeach
		@if(!$matrix)
			@include('maaltijden.abonnement.beheer_abonnement_head', ['repetities' => $repetities])
			<tbody>
			@endif
			</tbody>
	</table>
@endsection
