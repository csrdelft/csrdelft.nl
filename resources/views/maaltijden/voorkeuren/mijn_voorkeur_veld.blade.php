@if(is_null($uid))
<td id="voorkeur-row-{{$crv_repetitie_id}}" class="voorkeur-uitgeschakeld">
	<a href="/corvee/voorkeuren/inschakelen/{{$crv_repetitie_id}}" class="btn post voorkeur-uitgeschakeld"><input type="checkbox" /> Nee</a>
</td>
@else
<td id="voorkeur-row-{{$crv_repetitie_id}}" class="voorkeur-ingeschakeld">
	<a href="/corvee/voorkeuren/uitschakelen/{{$crv_repetitie_id}}" class="btn post voorkeur-ingeschakeld"><input type="checkbox" checked="checked" /> Ja</a>
</td>
@endif
