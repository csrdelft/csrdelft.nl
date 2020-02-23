@foreach($voorkeuren as $voorkeur)
	@if($voorkeur)
		@if($loop->first)
			<tr id="voorkeur-row-{{$voorkeur->getVanUid()}}">
				<td>{!! \CsrDelft\repository\ProfielRepository::getLink($voorkeur->getVanUid(),instelling('corvee', 'weergave_ledennamen_beheer')) !!}</td>
				@endif
				@include('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $voorkeur->crv_repetitie_id, 'uid' => $voorkeur->uid])
				@if($loop->last)
			</tr>
		@endif
	@endif
@endforeach
