<tr class="document">
	<td>
		@if($document->hasFile())
			<a href="{{$document->getUrl()}}" target="_blank">
				{{ $document->naam }}
			</a>
		@else
			<a title="Bestand niet gevonden..." class="filenotfound">
				{{ $document->naam }}
			</a>
		@endif

		@if($document->magVerwijderen())
			<a class="verwijderen" href="/documenten/verwijderen/{{$document->id}}" title="Document verwijderen"
				 onclick="return confirm('Weet u zeker dat u dit document wilt verwijderen')">@icon('verwijderen')</a>
		@endif
		@if($document->magBewerken())
			<a class="bewerken" href="/documenten/bewerken/{{$document->id}}"
				 title="Document bewerken">@icon('bewerken')</a>
		@endif
	</td>
	<td class="size">{{format_filesize($document->filesize)}}</td>
	<td title="{{$document->mimetype}}">{!! $document->getMimetypeIcon() !!}</td>
	<td>{!! reldate($document->toegevoegd) !!}</td>
	<td>{!! CsrDelft\model\ProfielModel::getLink($document->eigenaar, 'civitas') !!}</td>
</tr>

