@extends('plain')
@section('titel', $titel . " " . strftime("%A %e %B", strtotime($maaltijd->datum)))

@section('content')
	<h1>{{$titel}} op {{strftime("%A %e %B %Y", strtotime($maaltijd->datum))}}</h1>
	<div
		class="header">{!! str_replace("MAALTIJDPRIJS", $prijs, instelling('maaltijden', 'maaltijdlijst_tekst')) !!}</div>
	@if(!$maaltijd->gesloten)
		<h1 id="gesloten-melding">De maaltijd is nog niet gesloten
			@if(!$maaltijd->verwijderd and !$maaltijd->gesloten)
				&nbsp;
				<button href="/maaltijden/lijst/sluit/{{$maaltijd->maaltijd_id}}" class="btn btn-primary post confirm"
								title="Het sluiten van de maaltijd betekent dat niemand zich meer kan aanmelden voor deze maaltijd">
					Inschrijving sluiten
				</button>
			@endif
		</h1>
	@endif
	@if($maaltijd->getAantalAanmeldingen() > 0)
		<div class="w-100" style="column-count: 3">
			@foreach($aanmeldingen as $aanmelding)
				<div class="row no-gutters py-1" style="border-bottom: 1px solid #999;">
					@if($aanmelding->uid)
						<div class="col">
							{!! CsrDelft\model\ProfielModel::getLink($aanmelding->uid,instelling('maaltijden', 'weergave_ledennamen_maaltijdlijst')) !!}
							<br/>
							@php($eetwens = CsrDelft\model\ProfielModel::get($aanmelding->uid)->eetwens)
							@if($eetwens !== '')
								<strong class="eetwens">{{$eetwens}}</strong>
							@endif
							@if(! CsrDelft\model\ProfielModel::get($aanmelding->uid)->propertyMogelijk("eetwens") )
								<strong class="geeneetwens">Let op!</strong> Van deze gast is geen eetwens of allergie bekend (vanwege
								de
								lidstatus). Neem contact met deze persoon op voor informatie.
							@endif
							@if($aanmelding->gasten_eetwens !== '')
								@if($eetwens !== '')
									<br/>
								@endif
								<span class="opmerking">Gasten: </span>
								<strong class="eetwens">{{$aanmelding->gasten_eetwens}}</strong>
							@endif
						</div>
						<div class="col-auto">{{$aanmelding->getSaldoMelding()}}</div>
					@elseif($aanmelding->door_uid)
						<div class="col">Gast
							van {!! CsrDelft\model\ProfielModel::getLink($aanmelding->door_uid,instelling('maaltijden', 'weergave_ledennamen_maaltijdlijst')) !!}</div>
						<div class="col-auto">-</div>
					@else
						<div class="col" style="line-height: 2.2em;">&nbsp;</div>
						<div class="col-auto"></div>
					@endif
				</div>
			@endforeach
		</div>
	@else
		<p>Nog geen aanmeldingen voor deze maaltijd.</p>
	@endif
	<div class="w-100">
		<div class="row">
			<div class="col-md-4">
				<h3>Maaltijdgegevens</h3>
				<table>
					<tr>
						<td>Inschrijvingen:</td>
						<td>{{$maaltijd->getAantalAanmeldingen()}}</td>
					</tr>
					<tr>
						<td>Marge:</td>
						<td>{{$maaltijd->getMarge()}}</td>
					</tr>
					<tr>
						<td>Eters:</td>
						<td>{{$eterstotaal}}</td>
					</tr>
					<tr>
						<td>Budget koks:</td>
						<td>&euro; {{sprintf("%.2f", $maaltijd->getBudget())}}</td>
					</tr>
				</table>
			</div>
			<div class="col-md-8">
				<h3>Corvee</h3>
				@if($corveetaken)
					<div style="column-count: 2">
						@foreach($corveetaken as $taak)
							<div>
								@if($taak->uid)
									{!! CsrDelft\model\ProfielModel::getLink($taak->uid,instelling('maaltijden', 'weergave_ledennamen_maaltijdlijst')) !!}
								@else
									<span class="cursief">vacature</span>
								@endif
								&nbsp;({{$taak->getCorveeFunctie()->naam}})
							</div>
						@endforeach
					</div>
				@else
					<p>Geen corveetaken voor deze maaltijd in het systeem.</p>
				@endif
			</div>
		</div>
	</div>
@endsection
