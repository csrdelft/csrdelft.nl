<td id="maalcie-melding-veld">{!! getMelding() !!}</td>
@include('maaltijden.abonnement.beheer_abonnement_veld', ['abonnement' => $abonnement, 'uid' => $abonnement->uid, 'vanuid' => $abonnement->vanuid])
