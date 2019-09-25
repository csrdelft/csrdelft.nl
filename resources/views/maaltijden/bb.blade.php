<div class="media pt-3 maaltijdketzer-{{$maaltijd->maaltijd_id}}" data-maaltijdnaam="{{$maaltijd->titel}}">
	<div class="media-body pb-3 mb-0 lh-125 border-bottom border-gray">
		<div class="row">
			<div class="col">
				<h6>
					<a href="/maaltijden/ketzer">{{$maaltijd->titel}}</a>
					@if($maaltijd->getPrijs() !== intval(instelling('maaltijden', 'standaard_prijs')))
						&nbsp; (&euro; {{sprintf("%.2f", $maaltijd->getPrijsFloat()) }})
					@endif
				</h6>
				op {{strftime("%A %e %B", strtotime($maaltijd->datum)) }} om {{ strftime("%H:%M", strtotime($maaltijd->tijd)) }}
				@if($maaltijd->magBekijken(CsrDelft\model\security\LoginModel::getUid()))
					<div class="float-right">
						@icon("paintcan", null, $maaltijd->maaltijdcorvee->getCorveeFunctie()->naam)
					</div>
				@endif
				<div class="small">
					@if($maaltijd->magSluiten(CsrDelft\model\security\LoginModel::getUid()))
						<a href="/maaltijden/lijst/{{$maaltijd->maaltijd_id}}" title="Toon maaltijdlijst">
							@endif
							Inschrijvingen: <em>{{$maaltijd->getAantalAanmeldingen()}}</em> van <em>{{$maaltijd->aanmeld_limiet}}</em>
							@if($maaltijd->magSluiten(CsrDelft\model\security\LoginModel::getUid()))
						</a>
					@endif
				</div>
			</div>
			@can(P_LOGGED_IN)
				<div class="col-auto">
					@can(P_MAAL_IK)
						@if (!$maaltijd->gesloten)
							@if (isset($aanmelding))
								<a
									onclick="window.ketzerAjax('/maaltijden/ketzer/afmelden/{{$maaltijd->maaltijd_id}}', '.maaltijdketzer-{{$maaltijd->maaltijd_id}}');"
									class="btn btn-success aanmeldbtn" tabindex="0">
									<div class="form-check">
										<input class="form-check-input" type="checkbox" checked="checked"/>
										<label class="form-check-label">Aangemeld</label>
									</div>
								</a>

							@elseif ($maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet)
								@icon("stop", null, "Maaltijd is vol")&nbsp;
								<span class="maaltijd-afgemeld">Niet aangemeld</span>

							@else
								<a
									onclick="window.ketzerAjax('/maaltijden/ketzer/aanmelden/{{$maaltijd->maaltijd_id}}', '.maaltijdketzer-{{$maaltijd->maaltijd_id}}');"
									class="btn btn-danger aanmeldbtn" tabindex="0">
									<div class="form-check">
										<input class="form-check-input" type="checkbox"/>
										<label class="form-check-label">Niet aangemeld</label>
									</div>
								</a>
							@endif

						@else
							@if (isset($aanmelding))
								<span class="maaltijd-aangemeld">Aangemeld @if($aanmelding->door_abonnement) (abo) @endif</span>
							@else
								<span class="maaltijd-afgemeld">Niet aangemeld</span>
							@endif
						@endif

						@if(isset($aanmelding) && $aanmelding->aantal_gasten > 0)
							+{{$aanmelding->aantal_gasten}}
						@endif

						@if(isset($aanmelding) && $aanmelding->gasten_eetwens)
							@icon("comment", null, $aanmelding->gasten_eetwens)
						@endif

						@if($maaltijd->gesloten)
							@php($date = strftime("%H:%M", strtotime($maaltijd->laatst_gesloten)))
							@icon("lock", null, "Maaltijd is gesloten om " . $date)
						@endif
					@endcan
				</div>
		</div>
		@endcan
		{!! bbcode($maaltijd->omschrijving ?? "") !!}
	</div>
</div>
