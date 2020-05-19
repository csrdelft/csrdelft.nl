<?php
/**
 * @var \CsrDelft\entity\maalcie\Maaltijd $maaltijd
 * @var \CsrDelft\entity\maalcie\MaaltijdAanmelding $aanmelding
 */
?>
<tr id="maaltijd-row-{{$maaltijd->maaltijd_id}}"
		@if($maaltijd->aanmeld_limiet === 0 or ($maaltijd->gesloten and ! $aanmelding)) class="taak-grijs" @endif >
	<td>
		{{date_format_intl($maaltijd->datum, LONG_DATE_FORMAT)}} {{date_format_intl($maaltijd->tijd, TIME_FORMAT)}}
		@if($maaltijd->magBekijken(\CsrDelft\service\security\LoginService::getUid()))
			<div class="float-right">
				@icon("paintcan", null, $maaltijd->maaltijdcorvee->corveeFunctie->naam)
			</div>
		@endif
	</td>
	<td>
		<div class="titel">{{$maaltijd->titel}}
			<span title="BB-code: [maaltijd={{$maaltijd->maaltijd_id}}]"
						class="maaltijd-id"> (#{{$maaltijd->maaltijd_id}})</span>
			<div class="float-right">
				@php($prijs = sprintf("%.2f", $maaltijd->getPrijsFloat()))
				@if(!empty($aanmelding) && $aanmelding->getSaldoStatus() < 0)
					@icon("money_delete", null, "U hebt een negatief CiviSaldo!&#013;Maaltijdprijs: &euro; " . $prijs)
				@elseif(!empty($aanmelding) && $aanmelding->getSaldoStatus() < 2)
					@icon("money_delete", null, "Uw CiviSaldo is te laag!&#013;Maaltijdprijs: &euro; " . $prijs)
				@elseif($maaltijd->getPrijs() != $standaardprijs)
					@icon("money", null, "Afwijkende maaltijdprijs: &euro; " . $prijs)
				@else
					@icon("money_euro", null, "Maaltijdprijs: &euro; " . $prijs)
				@endif
			</div>
		</div>
		{!! bbcode($maaltijd->omschrijving ?? "") !!}
	</td>
	<td class="text-center">
		{{$maaltijd->getAantalAanmeldingen()}} ({{$maaltijd->aanmeld_limiet}})
		@if($maaltijd->magSluiten(\CsrDelft\service\security\LoginService::getUid()))
			<div class="float-right">
				<a href="/maaltijden/lijst/{{$maaltijd->maaltijd_id}}" title="Toon maaltijdlijst" class="btn">@icon("table")</a>
			</div>
		@endif
	</td>
	@if(!empty($aanmelding))
		@if($maaltijd->gesloten)
			<td class="maaltijd-aangemeld">
				Ja
				@if($aanmelding->door_abonnement) (abo) @endif
				<div class="float-right">
					@icon("lock", null, "Maaltijd is gesloten om " . date_format_intl($maaltijd->laatst_gesloten, TIME_FORMAT) . "")
				</div>
		@else
			<td class="maaltijd-aangemeld">
				<a href="/maaltijden/ketzer/afmelden/{{$maaltijd->maaltijd_id}}" class="btn post maaltijd-aangemeld">
					<input type="checkbox" checked="checked"/> Ja
				</a>
				@if($aanmelding->door_abonnement) (abo) @endif
				@endif
			</td>
			<td class="maaltijd-gasten">
				@if($maaltijd->gesloten)
					{{$aanmelding->aantal_gasten}}
				@else
					<div class="InlineForm">
						<div class="InlineFormToggle maaltijd-gasten">{{$aanmelding->aantal_gasten}}</div>
						<form action="/maaltijden/ketzer/gasten/{{$maaltijd->maaltijd_id}}" method="post"
									class="Formulier InlineForm ToggleForm">
							{!! printCsrfField() !!}
							<input type="text" name="aantal_gasten" value="{{$aanmelding->aantal_gasten}}"
										 origvalue="{{$aanmelding->aantal_gasten}}" class="FormElement" maxlength="4" size="4"/>
							<a class="btn submit" title="Wijzigingen opslaan">@icon("accept")</a>
							<a class="btn reset cancel" title="Annuleren">@icon("delete")</a>
						</form>
					</div>
				@endif
			</td>
			<td>
				@if($maaltijd->gesloten)
					@if($aanmelding->gasten_eetwens)
						@icon("comment", null, $aanmelding->gasten_eetwens)
					@endif
				@else
					@if($aanmelding->aantal_gasten > 0)
						<div class="InlineForm">
							<div class="InlineFormToggle" title="{{$aanmelding->gasten_eetwens}}">
								@if($aanmelding->gasten_eetwens)
									<a class="btn">@icon("comment_edit", null, $aanmelding->gasten_eetwens)</a>
								@else
									<a class="btn">@icon("comment_add", null, "Gasten allergie/diëet")</a>
								@endif
							</div>
							<form action="/maaltijden/ketzer/opmerking/{{$maaltijd->maaltijd_id}}" method="post"
										class="Formulier InlineForm ToggleForm">
								{!! printCsrfField("/maaltijden/ketzer/opmerking/{{$maaltijd->maaltijd_id}}") !!}
								<input type="text" name="gasten_eetwens" value="{{$aanmelding->gasten_eetwens}}"
											 origvalue="{{$aanmelding->gasten_eetwens}}" class="FormElement" maxlength="255" size="20"/>
								<a class="btn submit" title="Wijzigingen opslaan">@icon("accept")</a>
								<a class="btn reset cancel" title="Annuleren">@icon("delete")</a>
							</form>
						</div>
					@endif
				@endif
			</td>
			@else
				@if($maaltijd->gesloten or $maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet)
					<td class="maaltijd-afgemeld">
						@if(!$maaltijd->gesloten and $maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet)
							@icon("stop", null, "Maaltijd is vol")&nbsp;
						@endif
						Nee
						@if($maaltijd->gesloten)
							<span class="float-right">
							@icon("lock", null, "Maaltijd is gesloten om " . date_format_intl($maaltijd->laatst_gesloten, TIME_FORMAT) . "")
						</span>
				@endif
				@else
					<td class="maaltijd-afgemeld">
						<a href="/maaltijden/ketzer/aanmelden/{{$maaltijd->maaltijd_id}}" class="btn post maaltijd-afgemeld">
							<input type="checkbox"/> Nee
						</a>
						@endif
					</td>
					<td>-</td>
					<td></td>
				@endif
</tr>
