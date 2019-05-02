<?php
/**
 * @var \CsrDelft\model\entity\profiel\Profiel $profiel
 * @var \CsrDelft\model\entity\groepen\Bestuur[] $besturen
 * @var \CsrDelft\model\entity\groepen\Commissie[] $commissies
 * @var \CsrDelft\model\entity\groepen\Werkgroep[] $werkgroepen
 * @var \CsrDelft\model\entity\groepen\Ondervereniging[] $onderverenigingen
 * @var \CsrDelft\model\entity\groepen\RechtenGroep[] $groepen
 * @var \CsrDelft\model\entity\groepen\Ketzer[] $ketzers
 * @var \CsrDelft\model\entity\groepen\Activiteit[] $activiteiten
 * @var object[] bestellinglog
 * @var string $bestellingenlink
 * @var \CsrDelft\model\entity\maalcie\CorveeTaak[] $corveetaken
 * @var \CsrDelft\model\entity\maalcie\CorveeVoorkeur $corveevoorkeuren
 * @var \CsrDelft\model\entity\maalcie\CorveeVrijstelling $corveevrijstelling
 * @var \CsrDelft\model\entity\maalcie\CorveeKwalificatie $corveekwalificaties
 * @var int $forumpostcount
 * @var \CsrDelft\model\entity\forum\ForumPost[] $forumrecent
 * @var \CsrDelft\model\entity\bibliotheek\BoekExemplaar[] $boeken
 * @var \CsrDelft\model\entity\maalcie\MaaltijdAanmelding[] $recenteAanmeldingen
 * @var \CsrDelft\model\entity\maalcie\MaaltijdAbonnement[] $abos
 * @var \CsrDelft\model\entity\bibliotheek\BoekRecensie[] $gerecenseerdeboeken
 * @var \CsrDelft\view\View $fotos
 */
?>

@extends('layout')

@section('titel', 'Het profiel van '. $profiel->getNaam('volledig'))

@section('breadcrumbs')
	<div class="breadcrumbs"><a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> »
		<span class="active">{{$profiel->getNaam('civitas')}}</span></div>
@endsection

@section('content')
	<div id="profiel" @if($profiel->isJarig())class="jarig" @endif >
		<div id="profielregel">
			<div class="naam">
				<div class="float-right">
					<div class="pasfoto float-left">{!! $profiel->getPasfotoTag(false) !!}</div>
					<div class="knopjes">
						{{--{*<a href="/geolocation/map/{$profiel->uid}" class="btn" title="Huidige locatie op kaart tonen">{icon get="map"}</a>*}--}}
						@if($profiel->isInGoogleContacts())
							<a href="/profiel/{{$profiel->uid}}/addToGoogleContacts/" class="btn btn-light"
								 title="Dit profiel opdateren in mijn google adresboek">
								<img src="/images/google.ico" width="16" height="16" alt="opdateren in Google contacts"/>
							</a>
						@else
							<a href="/profiel/{{$profiel->uid}}/addToGoogleContacts/" class="btn btn-light"
								 title="Dit profiel toevoegen aan mijn google adresboek">
								<img src="/images/google.ico" width="16" height="16" alt="toevoegen aan Google contacts"/>
							</a>
						@endif
						@if($profiel->magBewerken())
							<a href="/profiel/{{$profiel->uid}}/bewerken" class="btn btn-light"
								 title="Bewerk dit profiel">@icon('pencil')</a>
							<a href="/profiel/{{$profiel->uid}}/voorkeuren" class="btn btn-light"
								 title="Pas voorkeuren voor commissies aan">@icon('report_edit')</a>
							<a href="/toestemming" class="btn btn-light" title="Pas toestemming aan">@icon('lock_edit')</a>
						@endif
						@if(mag(P_ADMIN) || is_ingelogd_account($profiel->uid))
							@if(\CsrDelft\model\security\AccountModel::existsUid($profiel->uid))
								<a href="/account/{{$profiel->uid}}/bewerken" class="btn btn-light"
									 title="Inloggegevens bewerken">@icon('key')</a>
							@else
								@can(P_ADMIN)
									<a href="/account/{{$profiel->uid}}/aanmaken" class="btn btn-light"
										 title="Account aanmaken">@icon('key_delete', 'key_add')</a>
								@endcan
							@endif
							@can(P_ADMIN)
								<a href="/tools/stats/?uid={{$profiel->uid}}" class="btn btn-light"
									 title="Toon bezoeklog">@icon('server_chart')</a>
							@endcan
						@endif
					</div>
				</div>
				{!! getMelding() !!}
				<h1 title="Lid-status: {{CsrDelft\model\entity\LidStatus::getDescription($profiel->status)}}">
					@if(\CsrDelft\model\entity\LidStatus::getChar($profiel->status) !== '')
						<span class="status">
						{{ CsrDelft\model\entity\LidStatus::getChar($profiel->status) }}&nbsp;
					</span>
					@endif
					{{$profiel->getNaam('volledig')}}
				</h1>
			</div>
		</div>

		<div class="profielregel gegevens row">
			<div class="col">
				<div class="label">Naam:</div>
				<div class="data">{{$profiel->getNaam('civitas')}}</div>
				<div class="label">Lidnummer:</div>
				<div class="data">
					@if(\CsrDelft\model\security\AccountModel::existsUid($profiel->uid) && \CsrDelft\model\security\LoginModel::instance()->maySuTo($profiel->getAccount()))
						<a href="/su/{{$profiel->uid}}/" title="Su naar dit lid">{{$profiel->uid}}</a>
					@else
						{{$profiel->uid}}
					@endif
				</div>
				@if($profiel->nickname)
					<div class="label">Bijnaam:</div>
					<div class="data">{{$profiel->nickname}}</div>
				@endif
				@if($profiel->duckname)
					<div class="label">Duckstad-naam:</div>
					<div class="data">{{$profiel->duckname}}</div>
				@endif
				<br/>
				@if($profiel->voorletters && is_zichtbaar($profiel, 'voorletters'))
					<div class="label">Voorletters:</div>
					<div class="data">{{$profiel->voorletters}}</div>
				@endif
				@if($profiel->gebdatum != '0000-00-00' && is_zichtbaar($profiel, 'gebdatum'))
					<div class="label">Geb.datum:</div>
					<div class="data">{{strftime('%d-%m-%Y', strtotime($profiel->gebdatum))}}</div>
				@endif
				@if($profiel->status === \CsrDelft\model\entity\LidStatus::Overleden && $profiel->sterfdatum !== '0000-00-00')
					<div class="label">Overleden op:</div>
					<div class="data">{{strftime('%d-%m-%y', strtotime($profiel->sterfdatum))}}</div>
				@endif
				@php($echtgenoot = \CsrDelft\model\ProfielModel::get($profiel->echtgenoot))
				@if($echtgenoot)
					<div class="label">
						@if($echtgenoot->geslacht === \CsrDelft\model\entity\Geslacht::Vrouw)
							Echtgenote: @else Echtgenoot:
						@endif
					</div>
					<div class="data">{!! $echtgenoot->getLink('civitas') !!}</div>
				@endif
			</div>
		</div>

		@if($profiel->status !== \CsrDelft\model\entity\LidStatus::Overleden && ($profiel->adres || $profiel->o_adres))
			<div class="profielregel gegevens row">
				<div class="col-md-6">
					@if($profiel->adres && is_zichtbaar($profiel, ['adres', 'postcode', 'woonplaats', 'land']))
						<div class="label">
							<a target="_blank"
								 href="https://maps.google.nl/maps?q={{urlencode($profiel->adres)}}+{{urlencode($profiel->woonplaats)}}+{{urlencode($profiel->land)}}"
								 title="Open kaart" class="lichtgrijs fa fa-map-marker fa-5x"></a>
						</div>
						<div class="data">
							@if($profiel->getWoonoord())
								<a href="{{$profiel->getWoonoord()->getUrl()}}" class="dikgedrukt">{{$profiel->getWoonoord()->naam}}</a>
								<br/>
							@endif
							{{$profiel->adres}}<br/>
							{{$profiel->postcode}} {{$profiel->woonplaats}}<br/>
							{{$profiel->land}}<br/>
							@if($profiel->telefoon){{$profiel->telefoon}}<br/>@endif
							@if($profiel->mobiel){{$profiel->mobiel}}<br/>@endif
						</div>
					@endif
				</div>
				@if($profiel->isLid() && $profiel->o_adres && is_zichtbaar($profiel, ['o_adres', 'o_postcode', 'o_woonplaats', 'o_land', 'o_telefoon']))
					<div class="col-md-6">
						<div class="label">
							<a target="_blank"
								 href="https://maps.google.nl/maps?q={{urlencode($profiel->o_adres)}}+{{urlencode($profiel->o_woonplaats)}}+{{urlencode($profiel->o_land)}}"
								 title="Open kaart" class="lichtgrijs fa fa-map-marker fa-5x"></a>
						</div>
						<div class="data">
							<strong>Ouders:</strong><br/>
							{{$profiel->o_adres}}<br/>
							{{$profiel->o_postcode}} {{$profiel->o_woonplaats}}<br/>
							{{$profiel->o_land}}<br/>
							{{$profiel->o_telefoon}}
						</div>
					</div>
				@endif
			</div>
		@endif

		<div class="profielregel gegevens row">
			<div class="col">
				@if(is_zichtbaar($profiel, 'email'))
					<div class="label">Email:</div>
					{{$profiel->primary_email}}<br/>
				@endif
				@if($profiel->linkedin)
					<div class="label">LinkedIn:</div>
					{{$profiel->linkedin}}<br/>
				@endif
				@if($profiel->website)
					<div class="label">Website:</div>
					{{$profiel->website}}<br/>
				@endif
			</div>
		</div>

		<div class="profielregel gegevens row">
			<div class="col-md-6">
				@if($profiel->studie && is_zichtbaar($profiel, 'studie'))
					<div class="label">Studie:</div>
					<div class="data">{{$profiel->studie}}</div>

					<div class="label">Studie sinds:</div>
					{{$profiel->studiejaar}}<br/>
				@endif
				<div class="label">Lid sinds:</div>
				@if($profiel->lidjaar)
					<a href="/ledenlijst?q=lichting:{{$profiel->lidjaar}}&amp;status=ALL"
						 title="Bekijk de leden van lichting {{$profiel->lidjaar}}">{{$profiel->lidjaar}}</a>
				@endif
				@if(!$profiel->isLid() && $profiel->lidafdatum != '0000-00-00') tot {{substr($profiel->lidafdatum,0,4)}} @endif
				<br/>
				<div class="label">Status:</div>
				{{\CsrDelft\model\entity\LidStatus::getDescription($profiel->status)}}<br/>
				<br/>
				@if($profiel->beroep && $profiel->isOudlid())
					<div class="label">Beroep/werk:</div>
					<div class="data">{{$profiel->beroep}}</div>
					<br/>
				@endif
			</div>
			@if(is_zichtbaar($profiel, ['kinderen'], 'intern') && is_zichtbaar($profiel, ['patroon'], 'profiel'))
				<div class="col-md-6">
					@php($patroon = \CsrDelft\model\ProfielModel::get($profiel->patroon))
					@if($patroon || $profiel->hasKinderen())
						<a class="float-right lichtgrijs fa fa-tree fa-3x" href="/leden/stamboom/{{$profiel->uid}}"
							 title="Stamboom van {{$profiel->getNaam()}}"></a>
					@endif
					@if($patroon)
						<div class="label">
							@if($patroon->geslacht === \CsrDelft\model\entity\Geslacht::Vrouw)
								Matroon:
							@else
								Patroon:
							@endif
						</div>
						<div class="data">
							{!! $patroon->getLink('civitas') !!}
						</div>
					@endif
					@if($profiel->hasKinderen())
						<div class="label">Kinderen:</div>
						<div class="data">
							@foreach($profiel->getKinderen() as $kind)
								{!! $kind->getLink('civitas') !!}<br/>
							@endforeach
						</div>
					@endif
				</div>
			@endif
		</div>

		<div class="profielregel clear-right">
			<div class="gegevens row">
				<div class="col-md-6">
					@if($profiel->verticale && is_zichtbaar($profiel, 'verticale', 'intern'))
						<div class="label">Verticale:</div>
						<div class="data">
							<a href="/ledenlijst?q=verticale:{{$profiel->verticale }}">{{$profiel->getVerticale()->naam}}</a>
						</div>
					@endif
					@if($profiel->moot)
						<div class="label">Oude moot:</div>
						<div class="data"><a href="/ledenlijst?q=moot:{{$profiel->moot}}">{{$profiel->moot}}</a></div>
					@endif
				</div>
				<div class="col-md-6">
					@if($profiel->getKring() && is_zichtbaar($profiel, 'kring', 'intern'))
						<div class="label">Kring:</div>
						<div class="data">
							<a href="{{$profiel->kring->getUrl()}}">{{$profiel->kring->naam}}
								@if($profiel->status === \CsrDelft\model\entity\LidStatus::Kringel)
									(kringel)
								@elseif($profiel->kring->getLid($profiel->uid)->opmerking === 'leider')
									(kringleider)
								@elseif($profiel->verticaleleider)
									(leider)
								@elseif($profiel->kringcoach)
									<span
										title="Kringcoach van verticale {{\CsrDelft\model\groepen\VerticalenModel::get($profiel->verticale)->naam}}">(kringcoach)</span>
								@endif
							</a>
						</div>
					@endif
				</div>
				<div class="clear-left"></div>
			</div>
		</div>

		<div class="profielregel gegevens row">
			<div class="col-md-6">
				@if($besturen)
					<div class="label">Bestuur:</div>
					<div class="data">
						@foreach($besturen as $bestuur)
							<a href="{{$bestuur->getUrl()}}">{{$bestuur->naam}}</a><br/>
						@endforeach
					</div>
					<br/>
				@endif
				@if($commissies && is_zichtbaar($profiel, 'commissies', 'intern'))
					<div class="label">Commissies:</div>
					<div class="data">
						@foreach($commissies as $commissie)
							<a href="{{$commissie->getUrl()}}">{{$commissie->naam}}</a><br/>
						@endforeach
					</div>
					<br/>
				@endif
				@if($onderverenigingen && is_zichtbaar($profiel, 'ondervereniging', 'intern'))
					<div class="label">Onder-<br/>verenigingen:</div>
					<div class="data">
						@foreach($onderverenigingen as $ondervereniging)
							<a href="{{$ondervereniging->getUrl()}}">{{$ondervereniging->naam}}</a><br/>
						@endforeach
					</div>
					<br/>
				@endif
				@if($groepen && is_zichtbaar($profiel, 'groepen', 'intern'))
					<div class="label">Overige<br/>groepen:</div>
					<div class="data">
						@foreach($groepen as $groep)
							<a href="{{$groep->getUrl()}}">{{$groep->naam}}</a><br/>
						@endforeach
					</div>
				@endif
			</div>
			<div class="col-md-6">
				@if($werkgroepen && is_zichtbaar($profiel, 'werkgroepen', 'intern'))
					<div class="label">Werkgroepen:</div>
					<div class="data">
						@foreach($werkgroepen as $werkgroep)
							<a href="{{$werkgroep->getUrl()}}">{{$werkgroep->naam}}</a><br/>
						@endforeach
					</div>
				@endif
			</div>
			@if(mag(P_LEDEN_MOD) || is_ingelogd_account($profiel->uid))
				<div class="col-12">
					<a class="btn btn-primary" onclick="$(this).parent().remove(); $('.meer-groepen').slideDown();" tabindex="0">
						Toon activiteiten
					</a>
				</div>
				<div class="col-md-6 meer-groepen" style="display: none">
					@if($ketzers)
						<div class="label">Aanschaf-<br/>ketzers:</div>
						<div class="data">
							@foreach($ketzers as $ketzer)
								<a href="{{$ketzer->getUrl()}}">{{$ketzer->naam}}</a><br/>
							@endforeach
						</div>
					@endif
				</div>
				<div class="col-md-6 meer-groepen" style="display: none">
					@if($activiteiten)
						<div class="label">Activiteiten:</div>
						<div class="data">
							@foreach($activiteiten as $activiteit)
								<a href="{{$activiteit->getUrl()}}">{{$activiteit->naam}}</a><br/>
							@endforeach
						</div>
					@endif
				</div>
			@endif
		</div>

		@if(($profiel->isLid() OR (mag(P_LEDEN_MOD) AND $profiel->getCiviSaldo())) AND $profiel->bankrekening)
			<div class="profielregel gegevens row">
				<div class="col-12">
					@if($profiel->bankrekening && is_zichtbaar($profiel, 'bankrekening', 'profiel_lid'))
						<div class="label">Bankrekening:</div>
						{{ $profiel->bankrekening }}
						@can(P_MAAL_MOD)
							<span class="lichtgrijs">(@if(!$profiel->machtiging)geen @endif machtiging getekend)</span>
						@endcan
					@endif
					<div class="clear-left"></div>
					@if(mag(P_FISCAAT_MOD) || is_ingelogd_account($profiel->uid))
						<a id="CiviSaldo"></a>
						<div class="label">Saldohistorie:</div>
						@foreach($bestellinglog as $bestelling)
							<div class="data @cycle("donker","licht")">
								<span>{{implode(", ", $bestelling->inhoud)}}</span>
								<span class="float-right">{{format_bedrag($bestelling->totaal)}}</span>
								<span
									class="float-right lichtgrijs bestelling-moment">({{strftime('%D', strtotime($bestelling->moment))}}) </span>
							</div>
						@endforeach
						<div class="data">
							<a href="{{$bestellingenlink}}">Meer &#187;</a>
						</div>
					@endif
				</div>
				@if(mag(P_FISCAAT_MOD) || is_ingelogd_account($profiel->uid))
					<div class="col-12 saldografiek">
						<div class="label">Saldografiek:</div>
						<div class="clear-left"></div>
						<div class="ctx-saldografiek verborgen" data-uid="{{$profiel->uid}}"
								 data-closed="{{json_encode(!is_ingelogd_account($profiel->uid))}}"
								 style="width: 670px;"></div>
					</div>
				@endif
			</div>
		@endif

		<div class="profielregel gegevens row" id="maaltijden">
			<div class="col-md-12">
				<div class="label">Allergie/dieet:</div>
				<div class="data">
					@if($profiel->eetwens && is_zichtbaar($profiel, 'eetwens') && is_zichtbaar($profiel, 'bijzonder', 'algemeen'))
						{{$profiel->eetwens}}
					@else
						-
					@endif
					@if(is_ingelogd_account($profiel->uid))
						<div class="inline" style="position: absolute;"><a href="/corveevoorkeuren" title="Bewerk voorkeuren"
																															 class="btn">@icon('pencil')</a></div>
					@endif
				</div>
			</div>
			@if(mag(P_MAAL_MOD) || is_ingelogd_account($profiel->uid))
				<div class="col-md-12">
					@if(isset($abos))
						<div class="label">Abo's:</div>
						<ul class="nobullets data">
							@foreach($abos as $abonnement)
								<li>{{$abonnement->maaltijd_repetitie->standaard_titel}}</li>
							@endforeach
						</ul>
					@endif
				</div>
				<div class="col-md-6">
					<div class="label">Corvee-<br/>voorkeuren:</div>
					<ul class="nobullets data">
						@foreach($corveevoorkeuren as $vrk)
							<li>
								{{$vrk->getCorveeRepetitie()->getDagVanDeWeekText()}} {{$vrk->getCorveeRepetitie()->getCorveeFunctie()->naam}}
							</li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-6">
					<div class="label">Recent:</div>
					<ul class="nobullets data">
						@foreach($recenteAanmeldingen as $aanmelding)
							<li>
								{{$aanmelding->maaltijd->getTitel()}} <span class="lichtgrijs">({{strftime('%a %e %b', strtotime($aanmelding->maaltijd->datum))}})</span>
							</li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-6">
					<div class="label">Corveepunten:</div>
					<div class="data">{{$profiel->corvee_punten}} @if($profiel->corvee_punten_bonus > 0)
							+ @endif @if($profiel->corvee_punten_bonus != 0){{$profiel->corvee_punten_bonus}} @endif</div>
				</div>
				<div class="col-md-6">
					<div class="label">Kwalificaties:</div>
					<ul class="nobullets data">
						@foreach($corveekwalificaties as $kwali)
							<li>{{$kwali->getCorveeFunctie()->naam}}<span
									class="lichtgrijs"> (sinds {{$kwali->wanneer_toegewezen}})</span></li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-12">
					<div class="label">Corveetaken:</div>
					<ul class="nobullets data">
						@foreach($corveetaken as $taak)
							<li>
								{{$taak->getCorveeFunctie()->naam}} <span class="lichtgrijs">({{strftime('%a %e %b', strtotime($taak->datum)) }})</span>
							</li>
						@endforeach
					</ul>
					<br/>
				</div>
			@endif
		</div>

		@if(is_ingelogd_account($profiel->uid))
			<div class="profielregel gegevens row" id="agenda">
				<div class="col" id="agenda_gegevens">
					<div class="label">Persoonlijke<br/>ICal-feed:</div>
					<div class="data">
						@if($profiel->getAccount()->hasPrivateToken())
							<input title="ICal-feed" class="form-control" type="text"
										 value="{{$profiel->getAccount()->getICalLink()}}"
										 onclick="this.setSelectionRange(0, this.value.length);" readonly/>
						@endif
						&nbsp;
						<small>Gebruikt dezelfde private token als het forum (zie hieronder)</small>
					</div>
					<br/>
				</div>
			</div>
		@endif

		@if($forumpostcount || is_ingelogd_account($profiel->uid))
			<div class="profielregel gegevens row" id="forum">
				<div class="col" id="forum_gegevens">
					@if(is_ingelogd_account($profiel->uid))
						<div class="label">Persoonlijk<br/>RSS-feed:</div>
						<div class="data">
							@if($profiel->getAccount()->hasPrivateToken())
								<input title="RSS-feed" class="form-control" type="text"
											 value="{{$profiel->getAccount()->getRssLink()}}"
											 onclick="this.setSelectionRange(0, this.value.length);" readonly/>
							@endif
							&nbsp; <a name="tokenaanvragen" class="btn" href="/profiel/{{$profiel->uid}}/resetPrivateToken">Nieuwe
								aanvragen</a>
						</div>
						<br/>
					@endif
					@if($forumpostcount && is_zichtbaar($profiel, 'forum_posts', 'intern'))
						<div class="label"># bijdragen:</div>
						<div class="data">
							{{$forumpostcount}} @if($forumpostcount > 1)berichten. @else bericht. @endif
						</div>
						<div class="label">Recent:</div>
						<div class="data">
							<table id="recenteForumberichten">
								@forelse($forumrecent as $post)
									<tr>
										<td><a href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}"
													 title="{{$post->tekst}}"
													 @if($post->getForumDraad()->isOngelezen())
													 class="{{\CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}}"
												@endif
											>
												{{truncate($post->getForumDraad()->titel, 75)}}
											</a>
										</td>
										<td>
											@if(\CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief')
												{!! reldate($post->datum_tijd) !!}
											@else
												{{$post->datum_tijd}}
											@endif
										</td>
									</tr>
								@empty
									<tr>
										<td>Geen bijdragen</td>
									</tr>
								@endforelse
							</table>
						</div>
					@endif
				</div>
			</div>
		@endif

		@if(!empty($boeken) || is_ingelogd_account($profiel->uid) || !empty($gerecenseerdeboeken))
			<div class="profielregel boeken gegevens row" id="boeken">
				<div class="col">
					@if($boeken)
						<div class="label">Boeken:</div>
						<ul class="nobullets data">
							@forelse($boeken as $exemplaar)
								@php($boek = $exemplaar->getBoek())
								<li>
									<a href="/bibliotheek/boek/{{$boek->id}}" title="Boek: {$boek->titel}}">
										<span title="boek" class="boekindicator">•</span><span
											class="titel">{{$boek->titel}}</span><span
											class="auteur">{{$boek->auteur}}</span>
									</a>
								</li>
							@empty
								<li>Geen boeken</li>
							@endforelse
						</ul>
					@endif
					@if(is_ingelogd_account($profiel->uid))
						<a class="btn" href="/bibliotheek/boek">@icon('book_add') Nieuw boek</a>
						<br/>
					@endif
					@if($gerecenseerdeboeken)
						<br/>
						<div class="label">Boekrecensies:</div>
						<ul class="nobullets data">
							@forelse($gerecenseerdeboeken as $exemplaar)
								@php($boek = $exemplaar->getBoek())
								<li>
									<a href="/bibliotheek/boek/{{$boek->id}}" title="Boek: {{$boek->titel}}">
										<span title="boek" class="boekindicator">•</span><span
											class="titel">{{$boek->titel}}</span><span
											class="auteur">{{$boek->auteur}}</span>
									</a>
								</li>
							@empty
								<li>Geen boeken</li>
							@endforelse
						</ul>
					@endif
				</div>
			</div>
		@endif

		@if(is_zichtbaar($profiel, 'fotos', 'intern'))
			<div class="profielregel fotos gegevens row" id="fotos">
				<div class="col">
					<div class="label">Fotoalbum:</div>
					<div class="row">
						@if(empty($fotos))
							Er zijn geen foto's gevonden met {{$profiel->getNaam('civitas')}} erop.
						@else
							@foreach($fotos as $foto)
								@php($foto->view())
							@endforeach
							<div class="w-100"></div>
							<a class="btn" href="/fotoalbum/{{$profiel->uid}}">Toon alle foto's</a>
						@endif
					</div>
				</div>
			</div>
		@endif

		@can(P_ADMIN . ',bestuur,commissie:NovCie')
			@if($profiel->status === \CsrDelft\model\entity\LidStatus::Noviet && $profiel->kgb)
				<div class="profielregel" id="novcieopmerking">
					<div style="cursor: pointer;" onclick="$('#novcie_gegevens').toggle();">NovCie-Opmerking &raquo;</div>
					<div class="gegevens verborgen" id="novcie_gegevens">{{bbcode($profiel->kgb)}}</div>
				</div>
			@endif
		@endcan

		@can(P_LEDEN_MOD)
			<div class="profielregel gegevens row" id="changelog">
				<div class="col">
					<div style="cursor: pointer;" onclick="$('#changelog_gegevens').toggle();this.remove()">
						Bewerklog &raquo;
					</div>
					<div class="verborgen" id="changelog_gegevens">
						@foreach(array_reverse($profiel->changelog) as $loggroup)
							{!! $loggroup->toHtml() !!}
						@endforeach
					</div>
				</div>
			</div>
		@endcan
	</div>
@endsection
