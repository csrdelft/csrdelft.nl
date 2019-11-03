<tr id="repetitie-row-{{$repetitie->crv_repetitie_id}}">
	<td>
		<a href="/corvee/repetities/bewerk/{{$repetitie->crv_repetitie_id}}" title="Corveerepetitie wijzigen"
			 class="btn post popup">@icon("pencil")</a>
		<a href="/corvee/functies/{{$repetitie->functie_id}}" title="Wijzig onderliggende functie" class="btn popup">@icon("cog_edit")</a>
		@if(empty($maaltijdrepetitie) && $repetitie->mlt_repetitie_id)
			<a href="/corvee/repetities/maaltijd/{{$repetitie->mlt_repetitie_id}}" title="Corveebeheer maaltijdrepetitie"
				 class="btn">@icon("calendar_link")</a>
		@endif
	</td>
	<td>{{$repetitie->getCorveeFunctie()->naam}}</td>
	<td>{{$repetitie->getDagVanDeWeekText()}}</td>
	<td>{{$repetitie->getPeriodeInDagenText()}}</td>
	<td>
		@if($repetitie->voorkeurbaar)
			@icon("tick", null, "Voorkeurbaar")
		@endif
	</td>
	<td>{{$repetitie->standaard_punten}}</td>
	<td>{{$repetitie->standaard_aantal}}</td>
	<td class="col-del">
		<a href="/corvee/repetities/verwijder/{{$repetitie->crv_repetitie_id}}"
			 title="Corveerepetitie definitief verwijderen" class="btn post confirm">@icon("cross")</a>
	</td>
</tr>
