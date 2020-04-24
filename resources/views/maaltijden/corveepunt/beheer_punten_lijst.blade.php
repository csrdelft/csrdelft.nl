<?php
/**
 * @var \CsrDelft\entity\corvee\CorveePuntenOverzicht $puntenlijst
 */
?>
<tr id="punten-row-{{$puntenlijst->lid->uid}}">
	<td>{{$puntenlijst->lid->getNaam(instelling('corvee', 'weergave_ledennamen_beheer'))}}</td>
	@foreach($puntenlijst->aantal as $fid => $aantal)
	<td>
		@if($aantal !== 0)
		{{$puntenlijst->punten[$fid]}}
		@endif
		@if($puntenlijst->bonus[$fid] > 0)
		+
		@endif
		@if($puntenlijst->bonus[$fid] !== 0)
		{{$puntenlijst->bonus[$fid]}}
		@endif
		@if($aantal !== 0)
		,{{$aantal}}
		@endif
	</td>
	@endforeach
	<td>
		<div class="InlineForm">
			<div class="InlineFormToggle">{{$puntenlijst->puntenTotaal}}</div>
			<form action="/corvee/punten/wijzigpunten/{{$puntenlijst->lid->uid}}" method="post" class="Formulier InlineForm ToggleForm">
				@php(printCsrfField())
				<input type="text" name="totaal_punten" value="{{$puntenlijst->puntenTotaal}}" origvalue="{{$puntenlijst->puntenTotaal}}" class="FormElement" maxlength="4" size="4" />
				<a class="btn submit" title="Wijzigingen opslaan">@icon("accept")</a>
				<a class="btn reset cancel" title="Annuleren" >@icon("delete")</a>
			</form>
		</div>
	</td>
	<td>
		<div class="InlineForm">
			<div class="InlineFormToggle">{{$puntenlijst->bonusTotaal}}</div>
			<form action="/corvee/punten/wijzigbonus/{{$puntenlijst->lid->uid}}" method="post" class="Formulier InlineForm ToggleForm">
				@php(printCsrfField())
				<input type="text" name="totaal_bonus" value="{{$puntenlijst->bonusTotaal}}" origvalue="{{$puntenlijst->bonusTotaal}}" class="FormElement" maxlength="4" size="4" />
				<a class="btn submit" title="Wijzigingen opslaan">@icon("accept")</a>
				<a class="btn reset cancel" title="Annuleren">@icon("delete")</a>
			</form>
		</div>
	</td>
	<td style="text-align: right; background-color: {{'#' . $puntenlijst->prognoseColor}};" @if($puntenlijst->vrijstelling) title="{{$puntenlijst->vrijstelling->percentage}}% vrijstelling" @endif >
	{{$puntenlijst->prognose}}
	<div class="inline" style="width: 25px;">
		@if($puntenlijst->vrijstelling)
			*
		@else
			&nbsp;
		@endif
	</div>
	</td>
</tr>
