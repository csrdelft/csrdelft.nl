<tr id="abonnement-row-{{$vanuid}}">
	@foreach($abonnementen as $abonnement)
		@if($loop->first)
			<td>{!! CsrDelft\model\ProfielModel::getLink($vanuid,instelling('maaltijden', 'weergave_ledennamen_beheer')) !!}</td>
		@endif
		@if($abonnement->maaltijd_repetitie && $abonnement->maaltijd_repetitie->abonneerbaar)
			@include('maaltijden.abonnement.beheer_abonnement_veld', ['uid' => $abonnement->uid, 'vanuid' => $vanuid, 'abonnement' => $abonnement])
		@else
			<td></td>
		@endif
	@endforeach
</tr>
