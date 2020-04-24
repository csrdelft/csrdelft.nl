<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeVrijstelling $vrijstelling
 */
?>
<tr id="vrijstelling-row-{{$vrijstelling->uid}}">
	<td>
		<a href="/corvee/vrijstellingen/bewerk/{{$vrijstelling->uid}}" title="Vrijstelling wijzigen" class="btn post popup">@icon("pencil")</a>
	</td>
	<td>{!! $vrijstelling->profiel->getLink(instelling('corvee', 'weergave_ledennamen_beheer')) !!}</td>
	<td>{{date_format_intl($vrijstelling->begin_datum, DATE_FORMAT)}}</td>
	<td>{{date_format_intl($vrijstelling->eind_datum, DATE_FORMAT)}}</td>
	<td>{{$vrijstelling->percentage}}%</td>
	<td>{{$vrijstelling->getPunten()}}</td>
	<td class="col-del">
		<a href="/corvee/vrijstellingen/verwijder/{{$vrijstelling->uid}}" title="Vrijstelling definitief verwijderen" class="btn post confirm">@icon("cross")</a>
	</td>
</tr>
