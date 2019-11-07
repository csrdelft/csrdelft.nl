<tr id="maalcie-melding">
	<td>{!! getMelding() !!}</td>
</tr>
@foreach($taken as $taak)
	@include('maaltijden.corveetaak.beheer_taak_lijst', ['show' => true, 'prullenbak' => false, 'taak' => $taak])
@endforeach
