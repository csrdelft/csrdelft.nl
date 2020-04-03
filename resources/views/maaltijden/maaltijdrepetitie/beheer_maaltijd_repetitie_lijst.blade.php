<?php
/**
 * @var \CsrDelft\entity\maalcie\MaaltijdRepetitie $repetitie
 */
?>
<tr id="repetitie-row-{{$repetitie->mlt_repetitie_id}}">
	<td>
		<a href="/maaltijden/repetities/bewerk/{{$repetitie->mlt_repetitie_id}}" title="Maaltijdrepetitie wijzigen" class="btn post popup">@icon("pencil")</a>
		<a href="/corvee/repetities/maaltijd/{{$repetitie->mlt_repetitie_id}}" title="Corveebeheer maaltijdrepetitie" class="btn popup">@icon("chart_organisation")</a>
	</td>
	<td>{{$repetitie->standaard_titel}}</td>
	<td>{{$repetitie->getDagVanDeWeekText()}}</td>
	<td>{{$repetitie->getPeriodeInDagenText()}}</td>
	<td>{{$repetitie->standaard_tijd->format(TIME_FORMAT)}}</td>
	<td>&euro; {{sprintf("%.2f", $repetitie->getStandaardPrijsFloat())}}</td>
	<td>{{$repetitie->standaard_limiet}}</td>
	<td>@if($repetitie->abonneerbaar)@icon("tick", null, "Abonneerbaar")@endif</td>
	<td>{{$repetitie->abonnement_filter}}</td>
	<td class="col-del"><a href="/maaltijden/repetities/verwijder/{{$repetitie->mlt_repetitie_id}}" title="Maaltijdrepetitie definitief verwijderen" class="btn post confirm">@icon("cross")</a></td>
</tr>
