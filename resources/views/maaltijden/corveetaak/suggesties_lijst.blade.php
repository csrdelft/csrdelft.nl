<br/>
<div id="suggesties" style="border: 1px solid #A9A9A9; ">
	<table class="maalcie-tabel" style="padding: 0;">
		<thead>
		<tr>
			<th style="width: 65px; padding-right: 0;">
				@if($kwalificatie_benodigd)
					Relatief
				@else
					Prognose
				@endif
			</th>
			<th style="width: 135px;">Naam</th>
			<th>Laatste taak
				@if($kwalificatie_benodigd)
					&nbsp;@icon("bullet_arrow_up")
				@endif
			</th>
		</tr>
		</thead>
	</table>
	<div class="scrollpane" id="suggesties-scrollpane" style="max-height:250px;">
		<table id="suggesties-tabel" class="maalcie-tabel">
			<tbody>
			@foreach($suggesties as $uid => $suggestie)
				<tr class="
							@if(!$suggestie["voorkeur"])  geenvoorkeur @endif
				@if($suggestie["recent"])  recent @endif
				@if($jongsteLichting === \CsrDelft\repository\ProfielRepository::get($uid)->lidjaar)  jongste @else oudere @endif
					">
					<td style="width: 15px;">
						<a class="btn submit" style="padding: 0 2px;"
							 onclick="$(this).closest('form').find('.LidField').val('{{$uid}}');">
							@if($suggestie["recent"])
								@icon("time_delete", null, "Recent gecorveed")
							@elseif($suggestie["voorkeur"])
								@icon("emoticon_smile", null, "Heeft voorkeur")
							@else
								@icon("bullet_go", null, "Toewijzen aan dit lid")
							@endif
						</a>
					</td>
					<td style="width: 30px; padding-right: 10px; text-align: right;">
						@if($kwalificatie_benodigd)
							@if($suggestie["relatief"] > 0) +@endif
							{{$suggestie["relatief"]}}
						@else
							{{$suggestie["prognose"]}}
						@endif
					</td>
					<td style="width: 140px;">
						{{\CsrDelft\repository\ProfielRepository::get($uid)->getNaam(instelling('corvee', 'weergave_ledennamen_beheer'))}}
					</td>
					@if($suggestie["laatste"])
						<td>{{strftime("%d %b %Y", $suggestie["laatste"]->getBeginMoment())}}</td>
						<td>{{$suggestie["laatste"]->getCorveeFunctie()->naam}}</td>
					@else
						<td colspan="2"></td>
					@endif
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>
<table id="suggesties-controls">
	<tr>
		<td @if(!empty($voorkeurbaar) AND !$voorkeurbaar)
				title="Deze corveerepetitie is niet voorkeurbaar."
				@elseif(empty($voorkeurbaar))
				title="Dit is geen periodieke taak dus zijn er geen voorkeuren."
			@endif
		>
			<input type="checkbox" id="voorkeur"
						 @if(!isset($voorkeurbaar) OR !$voorkeurbaar)
						 disabled
						 @else
						 @if($voorkeur)
						 checked="checked"
						 @endif
						 onchange="window.maalcie.takenToggleSuggestie('geenvoorkeur');"
				@endif
			/>
			<label for="voorkeur" class="CheckboxFieldLabel">Met voorkeur</label>
			<br/>
			<input type="checkbox" id="recent" onchange="window.maalcie.takenToggleSuggestie('recent');"
						 @if($recent)
						 checked="checked"
				@endif
			/>
			<label for="recent" class="CheckboxFieldLabel">Niet recent gecorveed</label>
		</td>
		<td>
			Toon novieten/sjaars<br/>

			<input type="radio" id="jongste_ja" name="jongste" value="ja" onchange="
								window.maalcie.takenToggleSuggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
								window.maalcie.takenToggleSuggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
							   " checked="checked"/>
			<label for="jongste_ja" class="KeuzeRondjeLabel">Ja</label>

			<input type="radio" id="jongste_nee" name="jongste" value="nee" onchange="
								window.maalcie.takenToggleSuggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
								window.maalcie.takenToggleSuggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
							   "/>
			<label for="jongste_nee" class="KeuzeRondjeLabel">Nee</label>

			<input type="radio" id="jongste_alleen" name="jongste" value="alleen" onchange="
								window.maalcie.takenToggleSuggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
								window.maalcie.takenToggleSuggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
							   "/>
			<label for="jongste_alleen" class="KeuzeRondjeLabel">Alleen</label>
		</td>
		<td style="width: 25px;">
			<br/>
			<a class="btn vergroot" data-vergroot="#suggesties-scrollpane" title="Uitklappen"><span
					class="fa fa-expand"></span></a>
		</td>
	</tr>
</table>
