<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeTaak $taak
 */
?>
<tr id="taak-datum-summary-{{$datum}}"
		class="taak-datum-summary taak-datum-{{$datum}}
		@if($datum < date_create_immutable('-1 day'))
		@if(!$show and !$prullenbak)  taak-datum-oud
	@endif  taak-oud
@endif
		@if($show)  verborgen
@endif " onclick="window.maalcie.takenToggleDatum('{{$datum}}');">
	<th colspan="7" class="@cycle('rowColor0','rowColor1')">
		@foreach($perdatum as $fid => $perfunctie)
			@foreach($perfunctie as $taak)
				@if($loop->first) {{-- eerste taak van functie: reset ingedeeld-teller --}}
				@php($count = 0)
				@if($loop->parent->first)
					<div class="inline niet-dik" style="width: 80px;">{{date_format_intl($taak->datum, LONG_DATE_FORMAT)}}</div>
				@endif
				<div class="inline" style="width: 70px;">
			<span title="{{$taak->getCorveeFunctie()->naam}}">
				&nbsp;{{$taak->getCorveeFunctie()->afkorting}}:&nbsp;
			</span>
					@endif
					@if($taak->uid) {{-- ingedeelde taak van functie: teller++ --}}
					@php($count = $count + 1)
					@endif
					@if($loop->last) {{-- laatste taak van functie: toon ingedeeld-teller en totaal aantal taken van deze functie --}}
					<span class="@if($count === $loop->count) functie-toegewezen @else functie-open @endif "
								style="background-color: inherit;">
				{{$count}}/{{$loop->count}}
			</span>
				</div>
				@endif
			@endforeach
		@endforeach
	</th>
</tr>
@include('maaltijden.corveetaak.beheer_taak_head', ['datum' => $datum, 'show' => $show, 'datum' => $datum])
