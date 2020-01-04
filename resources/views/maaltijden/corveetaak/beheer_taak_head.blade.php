<tr
	@if(isset($datum))
	id="taak-datum-head-{{$datum}}"
	class="taak-datum-head taak-datum-{{$datum}}
	@if(!$show)
		verborgen" onclick="window.maalcie.takenToggleDatum('{{$datum}}');
@endif "
	@endif >
	<th style="width: 100px;">Wijzig</th>
	<th>Gemaild</th>
	<th style="width: 70px;">Datum</th>
	<th>Functie</th>
	<th>Lid</th>
	<th>Punten<br/>toegekend</th>
	<th class="text-center">
		@if($prullenbak)
			@icon("cross", null, "Definitief verwijderen")
		@else
			@icon("bin_empty", null, "Naar de prullenbak verplaatsen")
		@endif
	</th>
</tr>
