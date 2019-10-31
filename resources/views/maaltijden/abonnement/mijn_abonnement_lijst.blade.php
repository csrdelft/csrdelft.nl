<tr>
	@include('maaltijden.abonnement.mijn_abonnement_veld', ['uid' => $abonnement->uid, 'mrid' => $abonnement->mlt_repetitie_id])
	<td>{{$abonnement->maaltijd_repetitie->standaard_titel}}</td>
	<td>{{$abonnement->maaltijd_repetitie->getDagVanDeWeekText()}}</td>
	<td>{{$abonnement->maaltijd_repetitie->getPeriodeInDagenText()}}</td>
</tr>
