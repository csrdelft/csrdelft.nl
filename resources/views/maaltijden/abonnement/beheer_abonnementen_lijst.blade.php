<tr id="maalcie-melding">
	<td id="maalcie-melding-veld">{!! getMelding() !!}</td>
</tr>
@foreach($matrix as $vanuid => $abonnementen)
	@include('maaltijden.abonnement.beheer_abonnement_lijst', ['vanuid' => $vanuid, 'abonnementen' => $abonnementen])
@endforeach
