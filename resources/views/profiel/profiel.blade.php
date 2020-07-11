<?php
/**
 * @var \CsrDelft\entity\profiel\Profiel $profiel
 * @var \CsrDelft\entity\groepen\Bestuur[] $besturen
 * @var \CsrDelft\entity\groepen\Commissie[] $commissies
 * @var \CsrDelft\entity\groepen\Werkgroep[] $werkgroepen
 * @var \CsrDelft\entity\groepen\Ondervereniging[] $onderverenigingen
 * @var \CsrDelft\entity\groepen\RechtenGroep[] $groepen
 * @var \CsrDelft\entity\groepen\Ketzer[] $ketzers
 * @var \CsrDelft\entity\groepen\Activiteit[] $activiteiten
 * @var \CsrDelft\entity\fiscaat\CiviBestelling[] $bestellinglog
 * @var string $bestellingenlink
 * @var \CsrDelft\entity\corvee\CorveeTaak[] $corveetaken
 * @var \CsrDelft\entity\corvee\CorveeVoorkeur[] $corveevoorkeuren
 * @var \CsrDelft\entity\corvee\CorveeVrijstelling $corveevrijstelling
 * @var \CsrDelft\entity\corvee\CorveeKwalificatie[] $corveekwalificaties
 * @var int $forumpostcount
 * @var \CsrDelft\entity\forum\ForumPost[] $forumrecent
 * @var \CsrDelft\entity\bibliotheek\BoekExemplaar[] $boeken
 * @var \CsrDelft\entity\maalcie\MaaltijdAanmelding[] $recenteAanmeldingen
 * @var \CsrDelft\entity\maalcie\MaaltijdAbonnement[] $abos
 * @var \CsrDelft\entity\bibliotheek\BoekRecensie[] $gerecenseerdeboeken
 * @var \CsrDelft\view\fotoalbum\FotoBBView[] $fotos
 */
?>

@extends('layout')

@section('titel', 'Het profiel van '. $profiel->getNaam('volledig'))

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/ledenlijst' => 'Leden',
	'' => $profiel->getNaam('civitas'),
	]) !!}
@endsection

@section('content')
	<div id="profiel" class="container {{$profiel->getProfielClasses()}}">
		<div id="profielregel">
			<div class="row">
				<h1 class="col" title="Lid-status: {{CsrDelft\model\entity\LidStatus::from($profiel->status)->getDescription()}}">
					@if(\CsrDelft\model\entity\LidStatus::from($profiel->status)->getChar() !== '')
						<span class="status">
						{{ CsrDelft\model\entity\LidStatus::from($profiel->status)->getChar() }}&nbsp;
					</span>
					@endif
					{{$profiel->getNaam('volledig')}}
				</h1>

				<div class="col-auto">
					<div class="btn-toolbar">
						<div class="btn-group">
							@if($profiel->isInGoogleContacts())
								<a href="/profiel/{{$profiel->uid}}/addToGoogleContacts" class="btn btn-light"
									 title="Dit profiel opdateren in mijn google adresboek">
									<img src="/images/google.ico" width="16" height="16" alt="opdateren in Google contacts"/>
								</a>
							@else
								<a href="/profiel/{{$profiel->uid}}/addToGoogleContacts" class="btn btn-light"
									 title="Dit profiel toevoegen aan mijn google adresboek">
									<img src="/images/google.ico" width="16" height="16" alt="toevoegen aan Google contacts"/>
								</a>
							@endif
							<a href="/profiel/{{$profiel->uid}}.vcf" class="btn btn-light"
								 title="Dit profiel opslaan in lokaal adresboek">
								@icon('vcard_add')
							</a>
						</div>
						<div class="btn-group ml-2">
							@if($profiel->magBewerken())
								<a href="/profiel/{{$profiel->uid}}/bewerken" class="btn btn-light"
									 title="Bewerk dit profiel">@icon('pencil')</a>
								<a href="/profiel/{{$profiel->uid}}/voorkeuren" class="btn btn-light"
									 title="Pas voorkeuren voor commissies aan">@icon('report_edit')</a>
								<a href="/toestemming" class="btn btn-light" title="Pas toestemming aan">@icon('lock_edit')</a>
							@endif
							@if(mag(P_ADMIN) || is_ingelogd_account($profiel->uid))
								@if(\CsrDelft\repository\security\AccountRepository::existsUid($profiel->uid))
									<a href="/account/{{$profiel->uid}}/bewerken" class="btn btn-light"
										 title="Inloggegevens bewerken">@icon('key')</a>
								@else
									@can(P_ADMIN)
										<a href="/account/{{$profiel->uid}}/aanmaken" class="btn btn-light"
											 title="Account aanmaken">@icon('key_delete', 'key_add')</a>
									@endcan
								@endif
								@can(P_ADMIN)
									<a href="/tools/stats?uid={{$profiel->uid}}" class="btn btn-light"
										 title="Toon bezoeklog">@icon('server_chart')</a>
								@endcan
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md"></div>
			<div class="col-md-auto">
				{!! $profiel->getPasfotoTag('rounded shadow-sm') !!}
			</div>
			<div class="col-md">
				@if(in_array('banaan', $profiel->getProfielOpties()))
					<img src="/dist/images/banaan.gif" alt="Dansende banaan" class="banaan clear">
				@endif
			</div>
		</div>

		<div class="row">
			<dl class="col-md-6">
				<dt>Naam</dt>
				<dd>{{$profiel->getNaam('civitas')}}</dd>
				<dt>Lidnummer</dt>
				<dd>
					@if($profiel->account && \CsrDelft\common\ContainerFacade::getContainer()->get(\CsrDelft\service\security\SuService::class)->maySuTo($profiel->account))
						<a href="?_switch_user={{$profiel->uid}}" title="Su naar dit lid">{{$profiel->uid}}</a>
					@else
						{{$profiel->uid}}
					@endif
				</dd>
				@if($profiel->nickname)
					<dt>Bijnaam:</dt>
					<dd>{{$profiel->nickname}}</dd>
				@endif
				@if($profiel->duckname)
					<dt>Duckstad-naam</dt>
					<dd>{{$profiel->duckname}}</dd>
				@endif
				@if($profiel->voorletters && is_zichtbaar($profiel, 'voorletters'))
					<dt>Voorletters</dt>
					<dd>{{$profiel->voorletters}}</dd>
				@endif
				@if($profiel->gebdatum && date_format_intl($profiel->gebdatum, DATE_FORMAT) != '0000-00-00' && is_zichtbaar($profiel, 'gebdatum'))
					<dt>Geboortedatum</dt>
					<dd>{{strftime('%d-%m-%Y', $profiel->gebdatum->getTimestamp())}}</dd>
				@endif
				@if($profiel->status === \CsrDelft\model\entity\LidStatus::Overleden && date_format_intl($profiel->sterfdatum, DATE_FORMAT) !== '0000-00-00')
					<dt>Overleden op</dt>
					<dd>{{strftime('%d-%m-%y', $profiel->sterfdatum->getTimestamp())}}</dd>
				@endif
				@php($echtgenoot = \CsrDelft\repository\ProfielRepository::get($profiel->echtgenoot))
				@if($echtgenoot)
					<dt>
						@if($echtgenoot->geslacht === \CsrDelft\entity\Geslacht::Vrouw())
							Echtgenote @else Echtgenoot
						@endif
					</dt>
					<dd>{!! $echtgenoot->getLink('civitas') !!}</dd>
				@endif
			</dl>
		</div>

		@if($profiel->status !== \CsrDelft\model\entity\LidStatus::Overleden && ($profiel->adres || $profiel->o_adres))
			<div class="row">
				<dl class="col-md-6">
					@if($profiel->adres && is_zichtbaar($profiel, ['adres', 'postcode', 'woonplaats', 'land']))
						<dt class="text-center">
							<a target="_blank"
								 href="https://maps.google.nl/maps?q={{urlencode($profiel->adres)}}+{{urlencode($profiel->woonplaats)}}+{{urlencode($profiel->land)}}"
								 title="Open kaart" class="lichtgrijs fa fa-map-marked fa-5x"></a>
						</dt>
						<dd>
							<ul class="list-unstyled">
								@if($profiel->getWoonoord())
									<li>
										<a href="{{$profiel->getWoonoord()->getUrl()}}" class="dikgedrukt">
											{{$profiel->getWoonoord()->naam}}
										</a>
									</li>
								@endif
								<li>{{$profiel->adres}}</li>
								<li>{{$profiel->postcode}} {{$profiel->woonplaats}}</li>
								<li>{{$profiel->land}}</li>
								@if($profiel->telefoon)
									<li>{{$profiel->telefoon}}</li>
								@endif
								@if($profiel->mobiel)
									<li>{{$profiel->mobiel}}</li>
								@endif
							</ul>
						</dd>
					@endif
				</dl>
				@if($profiel->isLid() && $profiel->o_adres && is_zichtbaar($profiel, ['o_adres', 'o_postcode', 'o_woonplaats', 'o_land', 'o_telefoon']))
					<dl class="col-md-6">
						<dt class="text-center">
							<a target="_blank"
								 href="https://maps.google.nl/maps?q={{urlencode($profiel->o_adres)}}+{{urlencode($profiel->o_woonplaats)}}+{{urlencode($profiel->o_land)}}"
								 title="Open kaart" class="lichtgrijs fa fa-map-marked fa-5x"></a>
						</dt>
						<dd>
							<ul class="list-unstyled">
								<li><strong>Ouders</strong></li>
								<li>{{$profiel->o_adres}}</li>
								<li>{{$profiel->o_postcode}} {{$profiel->o_woonplaats}}</li>
								<li>{{$profiel->o_land}}</li>
								<li>{{$profiel->o_telefoon}}</li>
							</ul>
						</dd>
					</dl>
				@endif
			</div>
		@endif

		<div class="row">
			<dl class="col-md-6">
				@if(is_zichtbaar($profiel, 'email'))
					<dt>Email</dt>
					<dd>{{$profiel->getPrimaryEmail()}}</dd>
					@if($profiel->sec_email)
						<dt></dt>
						<dd>{{$profiel->sec_email}}</dd>
					@endif

				@endif

				@if($profiel->linkedin)
					<dt>LinkedIn</dt>
					<dd>{{$profiel->linkedin}}</dd>
				@endif
				@if($profiel->website)
					<dt>Website</dt>
					<dd>{{$profiel->website}}</dd>
				@endif
			</dl>
		</div>

		<div class="row">
			<dl class="col-md-6">
				@if($profiel->studie && is_zichtbaar($profiel, 'studie'))
					<dt>Studie</dt>
					<dd>{{$profiel->studie}}</dd>

					<dt>Studie sinds</dt>
					<dd>{{$profiel->studiejaar}}</dd>
				@endif
				<dt>Lid sinds</dt>
				<dd>
					@if($profiel->lidjaar)
						<a href="/ledenlijst?q=lichting:{{$profiel->lidjaar}}&amp;status=ALL"
							 title="Bekijk de leden van lichting {{$profiel->lidjaar}}">{{$profiel->lidjaar}}</a>
					@endif
					@if(!$profiel->isLid() && $profiel->lidafdatum)
						tot {{$profiel->lidafdatum->format('Y')}}
					@endif
				</dd>
				<dt>Status</dt>
				<dd>{{\CsrDelft\model\entity\LidStatus::from($profiel->status)->getDescription()}}</dd>
				@if($profiel->beroep && $profiel->isOudlid())
					<dt>Beroep/werk</dt>
					<dd>{{$profiel->beroep}}</dd>
				@endif
			</dl>
			@if(is_zichtbaar($profiel, ['kinderen'], 'intern') && is_zichtbaar($profiel, ['patroon'], 'profiel'))
				<dl class="col-md-6">
					@if($profiel->getPatroonProfiel())
						<dt>
							@if($profiel->getPatroonProfiel()->geslacht->getValue() === \CsrDelft\entity\Geslacht::Vrouw)
								Matroon
							@else
								Patroon
							@endif
						</dt>
						<dd>
							{!! $profiel->getPatroonProfiel()->getLink('civitas') !!}
						</dd>
					@endif
					@if($profiel->hasKinderen())
						<dt>Kinderen</dt>
						<dd>
							<ul class="list-unstyled">
								@foreach($profiel->kinderen as $kind)
									<li>{!! $kind->getLink('civitas') !!}</li>
								@endforeach
							</ul>
						</dd>
					@endif
					@if($profiel->getPatroonProfiel() || $profiel->hasKinderen())
						<dt></dt>
						<dd>
							<a class="btn btn-light" href="/profiel/{{$profiel->uid}}/stamboom"
								 title="Stamboom van {{$profiel->getNaam()}}">
								<span class="fa fa-tree"></span>
								Stamboom bekijken
							</a>
						</dd>
					@endif
				</dl>
			@endif
		</div>

		<div class="row">
			<dl class="col-md-6">
				@if($profiel->verticale && is_zichtbaar($profiel, 'verticale', 'intern'))
					<dt>Verticale</dt>
					<dd>
						<a href="/ledenlijst?q=verticale:{{$profiel->verticale }}">{{$profiel->getVerticale()->naam}}</a>
					</dd>
				@endif
				@if($profiel->moot)
					<dt>Oude moot</dt>
					<dd><a href="/ledenlijst?q=moot:{{$profiel->moot}}">{{$profiel->moot}}</a></dd>
				@endif
			</dl>
			<dl class="col-md-6">
				@if($profiel->getKring() && is_zichtbaar($profiel, 'kring', 'intern'))
					<dt>Kring</dt>
					<dd>
						<a href="{{$profiel->getKring()->getUrl()}}">{{$profiel->getKring()->naam}}
							@if($profiel->status === \CsrDelft\model\entity\LidStatus::Kringel)
								(kringel)
							@elseif($profiel->getKring()->getLid($profiel->uid)->opmerking === 'leider')
								(kringleider)
							@elseif($profiel->verticaleleider)
								(leider)
							@elseif($profiel->kringcoach)
								<span
									title="Kringcoach van verticale {{\CsrDelft\common\ContainerFacade::getContainer()->get(\CsrDelft\repository\groepen\VerticalenRepository::class)->get($profiel->verticale)->naam}}">(kringcoach)</span>
							@endif
						</a>
					</dd>
				@endif
			</dl>
		</div>

		<div class="row">
			<dl class="col-md-6">
				@if($besturen)
					<dt>Bestuur</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($besturen as $bestuur)
								<li><a href="{{$bestuur->getUrl()}}">{{$bestuur->naam}}</a></li>
							@endforeach
						</ul>
					</dd>
				@endif
				@if($commissies && is_zichtbaar($profiel, 'commissies', 'intern'))
					<dt>Commissies</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($commissies as $commissie)
								<li><a href="{{$commissie->getUrl()}}">{{$commissie->naam}}</a></li>
							@endforeach
						</ul>
					</dd>
				@endif
				@if($onderverenigingen && is_zichtbaar($profiel, 'ondervereniging', 'intern'))
					<dt>Onderverenigingen</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($onderverenigingen as $ondervereniging)
								<li><a href="{{$ondervereniging->getUrl()}}">{{$ondervereniging->naam}}</a></li>
							@endforeach
						</ul>
					</dd>
				@endif
				@if($groepen && is_zichtbaar($profiel, 'groepen', 'intern'))
					<dt>Overigegroepen</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($groepen as $groep)
								<li><a href="{{$groep->getUrl()}}">{{$groep->naam}}</a></li>
							@endforeach
						</ul>
					</dd>
				@endif
			</dl>
			<dl class="col-md-6">
				@if($werkgroepen && is_zichtbaar($profiel, 'werkgroepen', 'intern'))
					<dt>Werkgroepen</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($werkgroepen as $werkgroep)
								<li><a href="{{$werkgroep->getUrl()}}">{{$werkgroep->naam}}</a></li>
							@endforeach
						</ul>
					</dd>
				@endif
			</dl>
			@if(mag(P_LEDEN_MOD) || is_ingelogd_account($profiel->uid))
				<div class="col-12 mb-3">
					<a class="btn btn-primary" href="#"
						 onclick="$(this).parent().remove(); $('.meer-groepen').slideDown();return false;" tabindex="0">
						Toon activiteiten
					</a>
				</div>
				<dl class="col-md-6 meer-groepen" style="display: none">
					@if($ketzers)
						<dt>Aanschafketzers</dt>
						<dd>
							<ul class="list-unstyled">
								@foreach($ketzers as $ketzer)
									<li><a href="{{$ketzer->getUrl()}}">{{$ketzer->naam}}</a></li>
								@endforeach
							</ul>
						</dd>
					@endif
				</dl>
				<dl class="col-md-6 meer-groepen" style="display: none">
					@if($activiteiten)
						<dt>Activiteiten</dt>
						<dd>
							<ul class="list-unstyled">
								@foreach($activiteiten as $activiteit)
									<li><a href="{{$activiteit->getUrl()}}">{{$activiteit->naam}}</a></li>
								@endforeach
							</ul>
						</dd>
					@endif
				</dl>
			@endif
		</div>

		@if(($profiel->isLid() OR (mag(P_LEDEN_MOD) AND $profiel->getCiviSaldo())) AND $profiel->bankrekening)
			<dl>
				@if($profiel->bankrekening && is_zichtbaar($profiel, 'bankrekening', 'profiel_lid'))
					<dt>Bankrekening</dt>
					<dd>
						{{ $profiel->bankrekening }}
						@can(P_MAAL_MOD)
							<span class="lichtgrijs">(@if(!$profiel->machtiging)geen @endif machtiging getekend)</span>
						@endcan
					</dd>
				@endif
				@if(mag(P_FISCAAT_MOD) || is_ingelogd_account($profiel->uid))
					<a id="CiviSaldo"></a>
					<dt>Saldohistorie</dt>
					<dd>
						<a class="btn btn-primary" href="#" onclick="$('#saldoTabel').show();$(this).hide();return false;">Toon
							recente bestellingen</a>
						<div id="saldoTabel" style="display: none;">
							<table class="table table-sm table-striped">
								@foreach($bestellinglog as $bestelling)
									<tr>
										<td>
											{{$bestelling->getInhoudTekst()}}
											@if($bestelling->comment)
												<br><i>{{$bestelling->comment}}</i>
											@endif
										</td>
										<td>{{format_bedrag($bestelling->totaal)}}</td>
										<td>({{date_format_intl($bestelling->moment, DATETIME_FORMAT)}})</td>
									</tr>
								@endforeach
							</table>
							<div class="text-right">
								<a href="{{$bestellingenlink}}">Meer &#187;</a>
							</div>
						</div>
					</dd>
			</dl>
		@endif
		@if(mag(P_FISCAAT_MOD) || is_ingelogd_account($profiel->uid))
			<dl>
				<dt>Saldografiek</dt>
				<dd>
					<div class="ctx-saldografiek verborgen" data-uid="{{$profiel->uid}}"
							 data-closed="{{json_encode(!is_ingelogd_account($profiel->uid))}}"></div>
				</dd>
			</dl>
		@endif
		@endif

		<div class="row" id="maaltijden">
			<dl class="col-md-6">
				<dt>Allergie/dieet</dt>
				<dd>
					@if($profiel->eetwens && is_zichtbaar($profiel, 'eetwens') && is_zichtbaar($profiel, 'bijzonder', 'algemeen'))
						{{$profiel->eetwens}}
					@else
						-
					@endif
					@if(is_ingelogd_account($profiel->uid))
						<div class="inline" style="position: absolute;"><a href="/corvee/voorkeuren" title="Bewerk voorkeuren"
																															 class="btn">@icon('pencil')</a></div>
					@endif
				</dd>
			</dl>
			@if(mag(P_MAAL_MOD) || is_ingelogd_account($profiel->uid))
				<dl class="col-md-6">
					@if(isset($abos))
						<dt>Abo's</dt>
						<dd>
							<ul class="list-unstyled">
								@foreach($abos as $abonnement)
									<li>{{$abonnement->maaltijd_repetitie->standaard_titel}}</li>
								@endforeach
							</ul>
						</dd>
					@endif
				</dl>
				<dl class="col-md-6">
					<dt>Corveevoorkeuren</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($corveevoorkeuren as $vrk)
								<li>
									{{$vrk->corveeRepetitie->getDagVanDeWeekText()}} {{$vrk->corveeRepetitie->corveeFunctie->naam}}
								</li>
							@endforeach
						</ul>
					</dd>
				</dl>
				<dl class="col-md-6">
					<dt>Recent</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($recenteAanmeldingen as $aanmelding)
								<li>
									{{$aanmelding->maaltijd->getTitel()}} <span class="lichtgrijs">({{date_format_intl($aanmelding->maaltijd->datum, LONG_DATE_FORMAT)}})</span>
								</li>
							@endforeach
						</ul>
					</dd>
				</dl>
				<dl class="col-md-6">
					<dt>Corveepunten</dt>
					<dd>{{$profiel->corvee_punten}} @if($profiel->corvee_punten_bonus > 0)
							+ @endif @if($profiel->corvee_punten_bonus != 0){{$profiel->corvee_punten_bonus}} @endif</dd>
					<dt>Corveetaken</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($corveetaken as $taak)
								<li>
									{{$taak->corveeFunctie->naam}} <span class="lichtgrijs">({{date_format_intl($taak->datum, LONG_DATE_FORMAT) }})</span>
								</li>
							@endforeach
						</ul>
					</dd>
				</dl>
				<dl class="col-md-6">
					<dt>Kwalificaties</dt>
					<dd>
						<ul class="list-unstyled">
							@foreach($corveekwalificaties as $kwali)
								<li>{{$kwali->corveeFunctie->naam}}<span
										class="lichtgrijs"> (sinds {{date_format_intl($kwali->wanneer_toegewezen, DATETIME_FORMAT)}})</span></li>
							@endforeach
						</ul>
					</dd>
				</dl>
			@endif
		</div>

		@if(is_ingelogd_account($profiel->uid))
			<dl id="agenda">
				<dt>Persoonlijke ICal-feed</dt>
				<dd>
					@if($profiel->account->hasPrivateToken())
						<input title="ICal-feed" class="form-control" type="text"
									 value="{{$profiel->account->getICalLink()}}"
									 onclick="this.setSelectionRange(0, this.value.length);" readonly/>
					@endif
					&nbsp;
					<small>Gebruikt dezelfde private token als het forum (zie hieronder)</small>
				</dd>
			</dl>
		@endif

		@if($forumpostcount || is_ingelogd_account($profiel->uid))
			<dl id="forum">
				@if(is_ingelogd_account($profiel->uid))
					<dt>Persoonlijk RSS-feed</dt>
					<dd>
						@if($profiel->account->hasPrivateToken())
							<input title="RSS-feed" class="form-control" type="text"
										 value="{{$profiel->account->getRssLink()}}"
										 onclick="this.setSelectionRange(0, this.value.length);" readonly/>
						@endif
						<a id="tokenaanvragen" class="btn btn-primary" href="/profiel/{{$profiel->uid}}/resetPrivateToken">
							Nieuwe aanvragen
						</a>
					</dd>
				@endif
				@if($forumpostcount && is_zichtbaar($profiel, 'forum_posts', 'intern'))
					<dt># bijdragen</dt>
					<dd>
						{{$forumpostcount}} @if($forumpostcount > 1)berichten. @else bericht. @endif
					</dd>
					<dt>Recent</dt>
					<dd>
						<table class="table table-sm table-striped">
							@forelse($forumrecent as $post)
								<tr>
									<td><a href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}"
												 title="{{$post->tekst}}"
												 @if($post->draad->isOngelezen())
												 class="{{lid_instelling('forum', 'ongelezenWeergave')}}"
											@endif
										>
											{{truncate($post->draad->titel, 75)}}
										</a>
									</td>
									<td>
										@if(lid_instelling('forum', 'datumWeergave') === 'relatief')
											{!! reldate($post->datum_tijd) !!}
										@else
											{{date_format_intl($post->datum_tijd, DATETIME_FORMAT)}}
										@endif
									</td>
								</tr>
							@empty
								<tr>
									<td>Geen bijdragen</td>
								</tr>
							@endforelse
						</table>
					</dd>
				@endif
			</dl>
		@endif

		@if(!empty($boeken) || is_ingelogd_account($profiel->uid) || !empty($gerecenseerdeboeken))
			<dl id="boeken" class="boeken">
				@if($boeken)
					<dt>Boeken</dt>
					<dd>
						<ul class="list-unstyled">
							@forelse($boeken as $exemplaar)
								@php($boek = $exemplaar->getBoek())
								<li>
									<a href="/bibliotheek/boek/{{$boek->id}}" title="Boek: {{$boek->titel}}">
										<span title="boek" class="boekindicator">•</span>
										<span class="titel">{{$boek->titel}}</span>
										<span class="auteur">{{$boek->auteur}}</span>
									</a>
								</li>
							@empty
								<li>Geen boeken</li>
							@endforelse
						</ul>
					</dd>
				@endif
				@if(is_ingelogd_account($profiel->uid))
					<dt></dt>
					<dd>
						<a class="btn btn-primary" href="/bibliotheek/boek">@icon('book_add') Nieuw boek</a>
					</dd>
				@endif
				@if($gerecenseerdeboeken)
					<dt>Boekrecensies</dt>
					<dd>
						<ul class="list-unstyled">
							@forelse($gerecenseerdeboeken as $exemplaar)
								@php($boek = $exemplaar->getBoek())
								<li>
									<a href="/bibliotheek/boek/{{$boek->id}}" title="Boek: {{$boek->titel}}">
										<span title="boek" class="boekindicator">•</span>
										<span class="titel">{{$boek->titel}}</span>
										<span class="auteur">{{$boek->auteur}}</span>
									</a>
								</li>
							@empty
								<li>Geen boeken</li>
							@endforelse
						</ul>
					</dd>
				@endif
			</dl>
		@endif

		@if(is_zichtbaar($profiel, 'fotos', 'intern'))
			<dl>
				<dt>Fotoalbum</dt>
				<dd>
					@if(empty($fotos))
						Er zijn geen foto's gevonden met {{$profiel->getNaam('civitas')}} erop.
					@else
						<div class="row">
							@foreach($fotos as $foto)
								<div class="col-md-2">
									@php($foto->view())
								</div>
							@endforeach
						</div>
						<a class="btn btn-primary" href="/fotoalbum/{{$profiel->uid}}">Toon alle foto's</a>
					@endif
				</dd>
			</dl>
		@endif

		@can(P_ADMIN . ',bestuur,commissie:NovCie')
			@if($profiel->status === \CsrDelft\model\entity\LidStatus::Noviet && $profiel->kgb)
				<div class="" id="novcieopmerking">
					<div style="cursor: pointer;" onclick="$('#novcie_gegevens').toggle();">NovCie-Opmerking &raquo;</div>
					<div class="gegevens verborgen" id="novcie_gegevens">{{bbcode($profiel->kgb)}}</div>
				</div>
			@endif
		@endcan

		@can(P_LEDEN_MOD)
			<div class="row" id="changelog">
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
