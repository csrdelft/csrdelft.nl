<tr id="vrijstelling-row-{{$vrijstelling->uid}}">
	<td>
		<a href="/corvee/vrijstellingen/bewerk/{{$vrijstelling->uid}}" title="Vrijstelling wijzigen" class="btn post popup">@icon("pencil")</a>
	</td>
	<td>{!! CsrDelft\model\ProfielModel::getLink($vrijstelling->uid,instelling('corvee', 'weergave_ledennamen_beheer')) !!}</td>
	<td>{{strftime("%e %b %Y", strtotime($vrijstelling->begin_datum))}}</td>
	<td>{{strftime("%e %b %Y", strtotime($vrijstelling->eind_datum))}}</td>
	<td>{{$vrijstelling->percentage}}%</td>
	<td>{{$vrijstelling->getPunten()}}</td>
	<td class="col-del">
		<a href="/corvee/vrijstellingen/verwijder/{{$vrijstelling->uid}}" title="Vrijstelling definitief verwijderen" class="btn post confirm">@icon("cross")</a>
	</td>
</tr>
