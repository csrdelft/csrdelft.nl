<tr id="corveefunctie-row-{{$functie->functie_id}}">
	<td>
		<a href="/corvee/functies/bewerken/{{$functie->functie_id}}" title="Functie wijzigen" class="btn post popup">@icon("pencil")</a>
	</td>
	<td>{{$functie->afkorting}}</td>
	<td>{{$functie->naam}}</td>
	<td>{{$functie->standaard_punten}}</td>
	<td title="{{$functie->email_bericht}}">@if(strlen($functie->email_bericht) > 0) @icon("email") @endif </td>
	<td>
		@if($functie->kwalificatie_benodigd)
		<div class="float-left"><a href="/corvee/functies/kwalificeer/{{$functie->functie_id}}" title="Kwalificatie toewijzen" class="btn post popup">@icon("vcard_add") Kwalificeer</a></div>
		@endif
		@if($functie->hasKwalificaties())
		<div class="kwali"><a title="Toon oudleden" class="btn" onclick="$('div.kwali').toggle();">@icon("eye") Toon oudleden</a></div>
		<div class="kwali verborgen"><a title="Toon leden" class="btn" onclick="$('div.kwali').toggle();">@icon("eye") Toon leden</a></div>
		@endif
		@foreach($functie->getKwalificaties() as $kwali)
		<div class="kwali @if(CsrDelft\model\ProfielModel::get($kwali->uid)->isOudlid()) verborgen @endif ">
			<a href="/corvee/functies/dekwalificeer/{{$functie->functie_id}}/{{$kwali->uid}}" title="Kwalificatie intrekken" class="btn post">@icon("vcard_delete")</a>
			&nbsp;{{CsrDelft\model\ProfielModel::get($kwali->uid)->getNaam(instelling('corvee', 'weergave_ledennamen_beheer'))}}
			<span class="lichtgrijs"> (sinds {{$kwali->wanneer_toegewezen}})</span>
		</div>
		@endforeach
	</td>
	<td title="Mag maaltijden sluiten">@if($functie->maaltijden_sluiten)@icon("lock_add")@endif</td>
	<td class="col-del">
		<a href="/corvee/functies/verwijderen/{{$functie->functie_id}}" title="Functie definitief verwijderen" class="btn post confirm">@icon("cross")</a>
	</td>
</tr>
