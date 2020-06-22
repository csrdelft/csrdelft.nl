@extends('fiscaat.base')

@section('titel', 'Bulk afschrijven CiviSaldo - Controle')

@section('civisaldocontent')
	<h2>Controleer de lijst</h2>
	<p>Controleer onderstaande lijst en klik op verwerk als deze klopt.</p>

	<div class="table-responsive-md">
		<table class="table table-striped">
			<thead>
			<tr>
				<th></th>
				<th>Account</th>
				<th>Product</th>
				<th>Aantal</th>
				<th>Totaal</th>
				<th>Nieuw saldo</th>
				<th>Beschrijving</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			@foreach($afschriften as $afschrift)
				<tr class="@if(!$afschrift->succes) table-warning @endif">
					<td><input type="checkbox" @if($afschrift->succes) checked @endif disabled></td>
					<td>{{$afschrift->accountNaam}}</td>
					<td>{{$afschrift->productNaam}}</td>
					<td>{{$afschrift->regel['aantal']}}</td>
					<td style="@if($afschrift->totaal < 0) color: red; @endif">{{sprintf('€%.2f', $afschrift->totaal)}}</td>
					@if($afschrift->succes)
						<td style="@if($afschrift->nieuwSaldo < 0) color: red; @endif">{{sprintf('€%.2f', $afschrift->nieuwSaldo)}}</td>
					@else
						<td></td>
					@endif
					<td>{{$afschrift->regel['beschrijving']}}</td>
					<td>{!!implode('<br>', $afschrift->waarschuwing)!!}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>

	<form action="/fiscaat/afschrijven/verwerk/{{$key}}" method="post" class="mt-4">
		<div class="form-check">
			<input onclick="checkDone()" type="checkbox" class="form-check-input" id="gecheckt" name="gecheckt">
			<label class="form-check-label" for="gecheckt">Ik heb bovenstaande lijst gecheckt.</label>
		</div>
		@if($aantalGefaald > 0)
			<div class="form-check">
				<input onclick="checkDone()" type="checkbox" class="form-check-input" id="foutenAkkoord" name="foutenAkkoord">
				<label class="form-check-label" for="foutenAkkoord">Ik begrijp dat {{$aantalGefaald}} regels met fouten niet verwerkt worden.</label>
			</div>
		@else
			<input type="hidden" name="foutenAkkoord" value="1">
		@endif
		@csrf
		<input class="btn btn-primary mt-2" id="verwerkKnop" type="submit" value="Vewerk {{$aantalSucces}} afschrijvingen" disabled>
	</form>
	<script>
		function checkDone() {
			const gecheckt = document.getElementById("gecheckt");
			const foutenAkkoord = document.getElementById("foutenAkkoord");
			const knop = document.getElementById("verwerkKnop");
			knop.disabled = (foutenAkkoord && !foutenAkkoord.checked) || !gecheckt.checked;
		}
	</script>
@endsection
