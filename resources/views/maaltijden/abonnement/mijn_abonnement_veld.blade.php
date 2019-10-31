@if(isset($uid))
	<td id="abonnement-cell-{{$mrid}}" class="abonnement-ingeschakeld">
		<a href="/maaltijden/abonnementen/uitschakelen/{{$mrid}}" class="btn post abonnement-ingeschakeld">
			<input type="checkbox" checked="checked"/> Aan
		</a>
	</td>
@else
	<td id="abonnement-cell-{{$mrid}}" class="abonnement-uitgeschakeld">
		<a href="/maaltijden/abonnementen/inschakelen/{{$mrid}}" class="btn post abonnement-uitgeschakeld">
			<input type="checkbox"/> Uit
		</a>
	</td>
@endif

