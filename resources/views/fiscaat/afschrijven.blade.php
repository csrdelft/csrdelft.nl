@extends('fiscaat.base')

@section('titel', 'Bulk afschrijven CiviSaldo')

@section('civisaldocontent')
	<h2>Bulk afschrijven</h2>
	<p>
		Met deze tool kan je op basis van een CSV-lijst bedragen afschrijven.
		Download hiervoor het template, vul de gegevens in en upload het bestand.
		Na een controle wordt elke regel als bestelling ingevoerd
		en worden de CiviSaldi van leden bijgewerkt.
	</p>
	<b>uid</b>
	<p>Dit is het UID van het account waarvan afgeschreven moet worden. Deze kan gevonden worden in <a href="/fiscaat/saldo" target="_blank">Saldo Beheer</a>. Bij leden is dit het lidnummer.</p>
	<b>productID</b>
	<p>Dit is het ID van het product dat je wilt afschrijven. Dit bepaalt daarmee ook de prijs. Het ID kan gevonden worden in <a href="/fiscaat/producten" target="_blank">Producten Beheer</a>. Let op dat je een product selecteert met status 1.</p>
	<b>aantal</b>
	<p>Dit is het aantal van het product dat je wilt afschrijven voor dit account. Samen met het productID bepaalt dit het bedrag.</p>
	<b>beschrijving</b>
	<p>Dit is de beschrijving die (naast de productnaam) zichtbaar wordt voor het lid. Maak het beschrijvend.</p>
	<a href="/fiscaat/afschrijven/template" class="btn btn-secondary">Download template</a>

	<form action="/fiscaat/afschrijven/upload" method="post" class="mt-4">
		<div class="form-group">
			<label for="uploadVeld">Example file input</label>
			<input id="uploadVeld" name="csv" type="file" accept=".csv,text/csv" class="form-control-file">
		</div>
		<input class="btn btn-primary" type="submit" value="Volgende">
	</form>
@endsection
