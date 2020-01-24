<tr id="corveetaak-row-{{$taak->taak_id}}" class="taak-datum-{{$taak->datum}}
@if(($taak->getBeginMoment() < strtotime('-1 day') and !empty($maaltijd)) or $taak->verwijderd)
	taak-oud
@endif
@if(!$show and !$prullenbak)
	verborgen
@endif
	">
	<td>
		@if($taak->verwijderd)
			<a href="/corvee/beheer/herstel/{{$taak->taak_id}}" title="Corveetaak herstellen" class="btn post">@icon("arrow_undo")</a>
		@else
			<a href="/corvee/beheer/bewerk/{{$taak->taak_id}}" title="Taak wijzigen"
				 class="btn post popup">@icon("pencil")</a>
			@if($taak->crv_repetitie_id)
				<a href="/corveerepetities/beheer/{{$taak->crv_repetitie_id}}" title="Wijzig gekoppelde corveerepetitie"
					 class="btn popup">@icon("calendar_edit")</a>
			@else
				<div class="inline" style="width: 28px;"></div>
			@endif
		@endif
		@if(empty($maaltijd) and $taak->maaltijd_id)
			<a href="/corvee/beheer/maaltijd/{{$taak->maaltijd_id}}" title="Beheer maaltijdcorvee" class="btn">@icon("cup_link")</a>
		@endif
	</td>
	<td class="text-center" style="width: 50px;">
		@php($aantal = $taak->getAantalKeerGemaild())
		@if(!$taak->verwijderd and (empty($maaltijd) or !$maaltijd->verwijderd))
			@php($wijzigbaar = true)
			@if($taak->uid)
				{{$aantal}}x
			@endif
			<div class="float-right">
				@if($taak->uid)
					<a href="/corvee/beheer/email/{{$taak->taak_id}}" title="Verstuur een (extra) herinnering voor deze taak"
						 class="btn post confirm">
						@endif
						@endif
						@if($taak->getIsTelaatGemaild())
							@icon("email_error", null, "Laatste herinnering te laat verstuurd!&#013;" . $taak->wanneer_gemaild . "")
						@elseif($aantal < 1)
							@if($taak->uid)
								@icon("email", null, "Niet gemaild")
							@endif
						@elseif($aantal === 1)
							@icon("email_go", null, $taak->wanneer_gemaild)
						@elseif($aantal > 1)
							@icon("email_open", null, $taak->wanneer_gemaild)
						@endif
						@if(!empty($wijzigbaar))
							@if($taak->uid)
					</a>
				@endif
			</div>
		@endif
	</td>
	<td>{{strftime("%a %e %b", strtotime($taak->datum))}}</td>
	<td style="width: 100px;">{{$taak->getCorveeFunctie()->naam}}</td>
	<td
		class="niet-dik
@if($taak->uid)
			taak-toegewezen
@elseif($taak->getBeginMoment() < strtotime(instelling('corvee', 'waarschuwing_taaktoewijzing_vooraf')))
			taak-warning
@else
			taak-open
@endif ">
		@if(!empty($wijzigbaar))
			<a href="/corvee/beheer/toewijzen/{{$taak->taak_id}}" id="taak-{{$taak->taak_id}}"
				 title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="btn post popup dragobject ruilen"
				 style="position: static;" @if($taak->uid)  uid="{{$taak->uid}}">
				@icon("user_green")
				@else
					> @icon("user_red")
				@endif
			</a>
		@endif
		@if($taak->uid)
			&nbsp;{!! \CsrDelft\repository\ProfielRepository::getLink($taak->uid,instelling('corvee', 'weergave_ledennamen_beheer')) !!}
		@endif
	</td>
	<td
		@if($taak->uid and ($taak->punten !== $taak->punten_toegekend or $taak->bonus_malus !== $taak->bonus_toegekend) and $taak->getBeginMoment() < strtotime(instelling('corvee', 'waarschuwing_puntentoewijzing_achteraf')))
		class="taak-warning"
		@endif >
	{{$taak->punten_toegekend}}
	@if($taak->bonus_toegekend > 0)
		+
	@endif
	@if($taak->bonus_toegekend !== 0)
		{{$taak->bonus_toegekend}}
	@endif
	&nbsp;van {{$taak->punten}}
	@if($taak->bonus_malus > 0)
		+
	@endif
	@if($taak->bonus_malus !== 0)
		{{$taak->bonus_malus}}
	@endif
	@if(!empty($wijzigbaar) and $taak->uid)
		<div class="float-right">
			@if($taak->wanneer_toegekend)
				<a href="/corvee/beheer/puntenintrekken/{{$taak->taak_id}}" title="Punten intrekken" class="btn post">@icon("medal_silver_delete")</a>
			@else
				<a href="/corvee/beheer/puntentoekennen/{{$taak->taak_id}}" title="Punten toekennen" class="btn post">@icon("award_star_add")</a>
			@endif
			@endif
		</div>
		</td>
		<td class="col-del">
			@if($taak->verwijderd)
				<a href="/corvee/beheer/verwijder/{{$taak->taak_id}}" title="Corveetaak definitief verwijderen"
					 class="btn post confirm range"><input type=checkbox id="box-{{$taak->taak_id}}" name="del-taak"/>@icon("cross")</a>
			@else
				<a href="/corvee/beheer/verwijder/{{$taak->taak_id}}" title="Corveetaak naar de prullenbak verplaatsen"
					 class="btn post range"><input type=checkbox id="box-{{$taak->taak_id}}" name="del-taak"/>@icon("bin_closed")</a>
			@endif
		</td>
</tr>
