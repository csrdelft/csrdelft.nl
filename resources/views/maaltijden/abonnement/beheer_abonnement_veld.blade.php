<td id="abonnement-cell-{{$vanuid}}-{{$abonnement->mlt_repetitie_id}}"
		class="
		@if($abonnement->foutmelding)
			abonnement-error
@elseif($abonnement->waarschuwing)
			abonnement-warning
@else
		@if($uid)
			abonnement-ingeschakeld
@else
			abonnement-uitgeschakeld
@endif
		@endif
			"
		title="{{$abonnement->foutmelding}}{{$abonnement->waarschuwing}}">
	<a
		href="
		@if($uid)
			/maaltijden/abonnementen/beheer/uitschakelen/{{$abonnement->mlt_repetitie_id}}/{{$vanuid}}
		@else
			/maaltijden/abonnementen/beheer/inschakelen/{{$abonnement->mlt_repetitie_id}}/{{$vanuid}}
		@endif
			"
		class="btn post
		@if($uid)
			abonnement-ingeschakeld
			@else
			abonnement-uitgeschakeld
			@endif
			">
		<input type="checkbox"
					 id="box-{{$vanuid}}-{{$abonnement->mlt_repetitie_id}}"
					 name="abo-{{$abonnement->mlt_repetitie_id}}"
					 @if($uid) checked="checked" @endif />
	</a>
</td>
