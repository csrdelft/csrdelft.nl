<?php
/**
 * @var $boek \CsrDelft\entity\bibliotheek\Boek
 * @var $boekFormulier \CsrDelft\view\bibliotheek\BoekFormulier
 * @var $exemplaarFormulieren \CsrDelft\view\bibliotheek\BoekExemplaarFormulier[]
 * @var $recensieFormulier \CsrDelft\view\bibliotheek\RecensieFormulier
 */
?>
@extends('layout')

@section('titel', 'Bibliotheek - Boek: ' . $boek->titel)

@section('content')
	<div class="foutje">{!! getMelding() !!}</div>
	<ul class="horizontal">
		<li>
			<a href="/bibliotheek" title="Naar de catalogus">Catalogus</a>
		</li>
		<li>
			<a href="/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>
		</li>
		<li>
			<a href="/bibliotheek/rubrieken" title="Rubriekenoverzicht">Rubrieken</a>
		</li>
	</ul>


	{{-- nieuw boek formulier --}}
	@if($boek->id==0)
		<div class="col-md-8">
			<h1>Nieuw boek toevoegen</h1>
			<p>Zoek via het Google Books-zoekveld je boek en kies een van de suggesties om de boekgegevens hieronder in te
				vullen.</p>
			<div class="boekzoeker form-group row"
					 title="Geef titel, auteur, isbn of een ander kenmerk van het boek. Minstens 7 tekens, na 1 seconde verschijnen suggesties.">
				<div class="col-3 col-form-label">
					<label for="boekzoeker"><img src="/images/google.ico" width="16" height="16" alt="Zoeken op Google Books"/>Google
						Books:</label>
				</div>
				<div class="col-9">
					<input type="text" class="form-control" placeholder="Zoek en kies een suggestie om de velden te vullen"
								 id="boekzoeker">
				</div>
			</div>

			@php($boekFormulier->view())
		</div>
	@else
		<div class="row">
			{{-- weergave bestaand boek, soms met bewerkbare velden --}}
			<div class="boek col-md-8" id="{{$boek->id}}">

				@if($boekFormulier->hasFields())

					{{$boekFormulier->view()}}

				@else
					<div class="row">
						<div class="col-3 col-form-label">Boek</div>
						<div class="col-9">{{$boek->titel}}</div>
					</div>
					@if($boek->auteur!='')
						<div class="row">
							<div class="col-3 col-form-label">Auteur</div>
							<div class="col-9">{{$boek->auteur}}</div>
						</div>@endif
					@if($boek->paginas!=0)
						<div class="row">
							<div class="col-3 col-form-label">Pagina's</div>
							<div class="col-9">{{$boek->paginas}}</div>
						</div>@endif
					@if($boek->taal!='')
						<div class="row">
							<div class="col-3 col-form-label">Taal</div>
							<div class="col-9">{{$boek->taal}}</div>
						</div>@endif
					@if($boek->isbn!='')
						<div class="row">
							<div class="col-3 col-form-label">ISBN</div>
							<div class="col-9">{{$boek->isbn}}</div>
						</div>@endif
					@if($boek->uitgeverij!='')
						<div class="row">
							<div class="col-3 col-form-label">Uitgeverij</div>
							<div class="col-9">{{$boek->uitgeverij}}</div>
						</div>@endif
					@if($boek->uitgavejaar!=0)
						<div class="row">
							<div class="col-3 col-form-label">Uitgavejaar</div>
							<div class="col-9">{{$boek->uitgavejaar}}</div>
						</div>@endif
					<div class="row">
						<div class="col-3 col-form-label">Rubriek</div>
						<div class="col-9">{{$boek->getRubriek()->__toString()}}</div>
					</div>
					@if($boek->code!='' && $boek->isBiebboek())
						<div class="row">
							<div class="col-3 col-form-label">Code</div>
							<div class="col-9">{{$boek->code}}</div>
						</div>@endif
				@endif

				<div class="clear-left"></div>

			</div>

			@if($boek->magBekijken())
				{{-- blok rechts met knopjes --}}
				<ul class="col-md-4 list-group">
					<li class="list-group-item"><a href="/bibliotheek/boek">@icon("book_add") Nieuw boek</a></li>
					@if($boek->id!=0)
						@if($boek->magVerwijderen())
							<li class="list-group-item"><a class="post verwijderen"
																						 href="/bibliotheek/verwijderboek/{{$boek->id}}"
																						 title="Boek verwijderen"
																						 onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen?')">@icon("verwijderen")
									Verwijderen</a></li>
						@endif
						<li class="list-group-item"><a class="post ReloadPage" href="/bibliotheek/addexemplaar/{{$boek->id}}"
																					 title="Ik bezit dit boek ook"
																					 onclick="return confirm('U bezit zelf een exemplaar van dit boek? Door het toevoegen aan de catalogus geef je aan dat anderen dit boek kunnen lenen.')">@icon("user_add")
								Ik bezit dit boek</a></li>
						@can(P_BIEB_MOD)
							<li class="list-group-item"><a class="post ReloadPage"
																						 href="/bibliotheek/addexemplaar/{{$boek->id}}/x222"
																						 title="C.S.R.-bieb bezit dit boek ook"
																						 onclick="return confirm('Bezit de C.S.R.-bieb een exemplaar van dit boek?')">@icon("user_add")
									Is een biebboek</a></li>
						@endcan
					@endif
				</ul>
			@endif
		</div>
		{{-- Exemplaren --}}
		<div class="row">
			@php($total_exemplaren_bibliotheek = 0) {{-- teller nodig om in compacte weergave slechts 1 biebboek te laten zien. --}}
			<div class="blok gegevens exemplaren col-md-6" id="exemplaren">
				<h3>Exemplaren</h3>
				@forelse($boek->getExemplaren() as $exemplaar)
					<div
						class="exemplaar uitgebreid @if(count($boek->getExemplaren())>4 && !$exemplaar->isEigenaar() && ($exemplaar->eigenaar_uid!='x222' || $total_exemplaren_bibliotheek>0 ))verborgen @endif ">
						<div
							class="fotolabel">{!! \CsrDelft\repository\ProfielRepository::getLink($exemplaar->eigenaar_uid, 'pasfoto') !!}</div>
						<div class="gegevensexemplaar" id="ex{{$exemplaar->id}}">
							{{-- eigenaar --}}
							<div class="regel">
								<label>Eigenaar</label>
								@if($exemplaar->eigenaar_uid=='x222')@php($total_exemplaren_bibliotheek += 1)
								C.S.R.-bibliotheek
								@else
									{!! \CsrDelft\repository\ProfielRepository::getLink($exemplaar->eigenaar_uid, 'civitas') !!}
								@endif
							</div>

							{{-- opmerking --}}
							@if($exemplaar->magBewerken())
								@php($exemplaarFormulieren[$exemplaar->id]->view())
							@else
								@if($exemplaar->opmerking != '')
									<div class="regel">
										<label>Opmerking</label><span class="opmerking">{{$exemplaar->opmerking}}</span>
									</div>
								@endif
							@endif
							{{-- status --}}
							<div class="regel">
								<label>Status</label>
								@if($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::uitgeleend())
									<span
										title="Sinds {{strip_tags(reldate($exemplaar->uitleendatum))}}">Uitgeleend aan {{\CsrDelft\repository\ProfielRepository::getLink($exemplaar->uitgeleend_uid, 'civitas')}}</span>
								@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::teruggegeven())
									<span
										title="Was uitgeleend sinds {{strip_tags(reldate($exemplaar->uitleendatum))}}">Teruggegeven door {{\CsrDelft\repository\ProfielRepository::getLink($exemplaar->uitgeleend_uid, 'civitas')}}</span>
								@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::vermist())
									<span class="waarschuwing"
												title="Sinds {{strip_tags(reldate($exemplaar->uitleendatum))}}">Vermist</span>
								@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::beschikbaar())
									Beschikbaar
								@endif
							</div>
							@if($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::beschikbaar() && $boek->isEigenaar($exemplaar->id))
								@php($boek->ajaxformuliervelden->findByName("lener_`$exemplaar->id`")->view())
							@endif
							{{-- actieknoppen --}}
							<div class="regel actieknoppen">
								<label>&nbsp;</label>
								<div>
									@if($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::beschikbaar())
										@if($exemplaar->eigenaar_uid=='x222') {{-- bibliothecaris werkt met kaartjes --}}
										@if(!$boek->isEigenaar($exemplaar->id)) {{-- BASFCie hoeft opmerking niet te zien --}}
										<span
											class="suggestie recht">Biebboek lenen: laat het kaartje achter voor de bibliothecaris.</span>
										<br/>
										@endif
										@else
											<a class="btn post ReloadPage" href="/bibliotheek/exemplaarlenen/{{$exemplaar->id}}"
												 title="Leen dit boek"
												 onclick="return confirm('U wilt dit boek van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} lenen?')">@icon("lorry")
												Exemplaar lenen</a>
										@endif
									@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::uitgeleend() && CsrDelft\model\security\LoginModel::getUid()==$exemplaar->uitgeleend_uid && $exemplaar->uitgeleend_uid!=$exemplaar->eigenaar_uid)
										<a class="btn post ReloadPage" href="/bibliotheek/exemplaarteruggegeven/{{$exemplaar->id}}"
											 title="Boek heb ik teruggegeven"
											 onclick="return confirm('U heeft dit exemplaar van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} teruggegeven?')">@icon("lorry_go")
											Teruggegeven</a>
									@endif
									@if($exemplaar->isEigenaar())
										@if(($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::uitgeleend() || $exemplaar->isTeruggegeven()))
											<a class="btn post ReloadPage" href="/bibliotheek/exemplaarterugontvangen/{{$exemplaar->id}}"
												 title="Boek is ontvangen"
												 onclick="return confirm('Dit exemplaar van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} is terugontvangen?')">@icon("lorry_flatbed")
												Ontvangen</a>
										@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::beschikbaar())
											<a class="btn post ReloadPage" href="/bibliotheek/exemplaarvermist/{{$exemplaar->id}}"
												 title="Exemplaar is vermist"
												 onclick="return confirm('Is het exemplaar van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} vermist?')">@icon("emoticon_unhappy")
												Vermist</a>
										@elseif($exemplaar->status===\CsrDelft\entity\bibliotheek\BoekExemplaarStatus::vermist())
											<a class="btn post ReloadPage" href="/bibliotheek/exemplaargevonden/{{$exemplaar->id}}"
												 title="Exemplaar teruggevonden"
												 onclick="return confirm('Is het exemplaar van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} teruggevonden?')">@icon("emoticon_smile")
												Teruggevonden</a>
										@endif
										<a class="btn post ReloadPage" href="/bibliotheek/verwijderexemplaar/{{$exemplaar->id}}"
											 title="Exemplaar verwijderen"
											 onclick="return confirm('Weet u zeker dat u dit exemplaar van {{\CsrDelft\repository\ProfielRepository::getNaam($exemplaar->eigenaar_uid)}} wilt verwijderen?')">@icon("verwijderen")
											Verwijderen</a>
									@endif
								</div>
							</div>
						</div>
					</div>
				@empty
					<p>Geen exemplaren.</p>
				@endforelse

				{{-- compacte weergave met alleen foto's --}}
				@php($total_exemplaren_biblitoheek = 0) {{-- teller nodig om in compacte weergave slechts 1 biebboek te laten zien. --}}
				@if(count($boek->getExemplaren())>4)
					<div class="exemplaar compact">
						@foreach($boek->getExemplaren() as $exemplaar)
							@if(!$boek->isEigenaar($exemplaar->id) && ($exemplaar->eigenaar_uid!='x222' || $total_exemplaren_bibliotheek>0 ))
								{{\CsrDelft\repository\ProfielRepository::getLink($exemplaar->eigenaar_uid, 'pasfoto')}}
								<div class="statusmarkering"><span class="biebindicator {{$exemplaar->status->getDescription()}}"
																									 title="Boek is {{$exemplaar->status->getDescription()}}">• </span></div>
							@endif
							@if($exemplaar->eigenaar_uid=='x222')
								@php($total_exemplaren_bibliotheek += 1)
							@endif
						@endforeach
						<br/>
						<div class="clear"></div>
						<label>&nbsp;</label><a
							onclick="jQuery(this).parent().parent().children('div.exemplaar->uitgebreid').show(); jQuery(this).parent().remove();">»
							meer informatie</a>
					</div>
				@endif
			</div>

			{{-- beschrijvingen --}}

			<div class="beschrijvingen col-md-6">
				<h3 class="header">Recensies en beschrijvingen</h3>
				<table id="beschrijvingentabel">
					@foreach($recensies as $beschrijving)
						<tr>
							<td class="linkerkolom"></td>
							<td style="width:506px"></td>
						</tr>
						<tr>
							<td class="linkerkolom recensist">
							<span
								class="recensist">{!! \CsrDelft\repository\ProfielRepository::getLink($beschrijving->schrijver_uid, 'civitas') !!}</span><br/>
								<span class="moment">{!! reldate($beschrijving->toegevoegd) !!}</span><br/>

								{{-- knopjes bij elke post --}}
								@if($beschrijving->magVerwijderen())
									<a
										href="/bibliotheek/verwijderbeschrijving/{{$boek->id}}/{{CsrDelft\model\security\LoginModel::getUid()}}"
										class="post ReloadPage"
										onclick="return confirm('Weet u zeker dat u deze beschrijving wilt verwijderen?')"><span
											class="ico cross  " title="Verwijderen"></span> Verwijderen</a>
								@endif
							</td>
							<td class="beschrijving @cycle('b0', 'b1')" id="beschrijving{{$beschrijving->id}}">
								@if($recensieFormulier->getModel() == $beschrijving)
									@php($recensieFormulier->view())
								@else
									{!! bbcode($beschrijving->beschrijving) !!}
								@endif
								@if($beschrijving->bewerkdatum!='0000-00-00 00:00:00')
									<br/>
									<span class="offtopic">Bewerkt {!! reldate($beschrijving->bewerkdatum) !!}</span>
								@endif
							</td>
						</tr>
						<tr>
							<td class="linkerkolom"></td>
							<td class="tussenschot"></td>
						</tr>
					@endforeach
					@if($recensieFormulier->isNieuw() )
						<tr>
							<td colspan="2">
								@php($recensieFormulier->view())
							</td>
						</tr>
					@endif
				</table>
			</div>
		</div>
	@endif
@endsection
