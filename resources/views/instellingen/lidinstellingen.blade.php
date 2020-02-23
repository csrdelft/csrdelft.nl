@extends('layout')

@section('titel', 'Lid instellingen')

@section('bodyArgs', 'data-offset="5" data-spy="scroll" data-target="#instellingen"')

@section('content')
	<div class="row">
		<div class="col">
			<h1>Lid instellingen</h1>

			<p>
				Deze instellingen zijn voor hoe de stek er voor jou uit ziet. Wees dus niet bang dat er iets kapot gaat als je
				deze instellingen veranderd.
			</p>

			<a href="{{REQUEST_URI}}">
				<div class="instellingen-bericht alert alert-warning d-none sticky-top">
					Er zijn instellingen veranderd, klik hier of ververs de pagina om de veranderingen te
					zien.
					@icon('page_error', 'page_refresh', 'Pagina verversen', 'float-right')
				</div>
			</a>

			@foreach($defaultInstellingen as $module => $moduleInstellingen)
				<h2 id="instelling-{{$module}}">{{ucfirst($module)}}</h2>

				@foreach($moduleInstellingen as $id => $instelling)
					@php
						$titel = $instelling['titel'];
						$type = $instelling['type'];
						$opties = $instelling['opties'];
					 	$default = $instelling['default'];
						$beschrijving = $instelling['beschrijving']
					@endphp
					@php($keuze = isset($instellingen[$module][$id]) ? $instellingen[$module][$id] : $default)
					<div class="form-group row instelling" id="instelling-{{$module}}-{{$id}}">
						<label class="col-md-3 col-form-label" for="inst_{{$module}}_{{$id}}">{!! $titel !!}</label>

						<div class="col-md-9">
							@if($type === \CsrDelft\model\instellingen\InstellingType::Enumeration)
								@if(count($opties) > 8)
									<select name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" class="form-control change-opslaan"
													data-href="/instellingen/update/{{$module}}/{{$id}}">
										@foreach($opties as $optie)
											<option value="{{$optie}}" @if($optie === $keuze) selected @endif>{{ucfirst($optie)}}</option>
										@endforeach
									</select>
								@else
									{{-- Verticaal op xs --}}
									<div class="btn-group-vertical btn-group-toggle d-inline-flex d-sm-none" data-buttons="radio">
										@foreach($opties as $optieId => $optie)
											@php($optieId = is_int($optieId) ? $optie : $optieId)
											<a class="post noanim instellingKnop btn btn-secondary @if($optie === $keuze) active @endif"
												 href="/instellingen/update/{{$module}}/{{$id}}/{{$optieId}}">{{ucfirst($optie)}}</a>
										@endforeach
									</div>
									{{-- Horizontaal op alle andere --}}
									<div class="d-none d-sm-inline-flex btn-group btn-group-toggle"
											 data-buttons="radio">
										@foreach($opties as $optieId => $optie)
											@php($optieId = is_int($optieId) ? $optie : $optieId)
											<a class="post noanim instellingKnop btn btn-secondary @if($optieId === $keuze) active @endif"
												 href="/instellingen/update/{{$module}}/{{$id}}/{{$optieId}}">{{ucfirst($optie)}}</a>
										@endforeach
									</div>
								@endif
							@elseif($type === \CsrDelft\model\instellingen\InstellingType::String)
								<input type="text" name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" value="{{$keuze}}"
											 data-href="/instellingen/update/{{$module}}/{{$id}}"
											 class="form-control change-opslaan" minlength="{{$opties[0]}}" maxlength="{{$opties[1]}}"/>
							@elseif($type === \CsrDelft\model\instellingen\InstellingType::Integer)
								<input type="number" name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" value="{{$keuze}}"
											 data-href="/instellingen/update/{{$module}}/{{$id}}"
											 class="form-control change-opslaan" data-href="/instellingen/update/{{$module}}/{{$id}}"
											 min="{{$opties[0]}}" max="{{$opties[1]}}"/>
							@else
								<div class="bg-danger">Voor dit type bestaat geen optie.</div>
							@endif
							<small class="form-text text-muted">
								@php($default = isset($opties[$default]) ? $opties[$default] : $default)
								{{$beschrijving}} Standaard waarde: "{{ucfirst($default)}}".
							</small>
						</div>
					</div>
				@endforeach
			@endforeach

			@php((new \CsrDelft\view\login\RememberLoginTable())->view())
			@php((new \CsrDelft\view\login\LoginSessionsTable())->view())
		</div>

		<div class="col-md-4 d-none d-lg-block">
			<div id="instellingen" class="sticky-top list-group">
				@foreach($defaultInstellingen as $module => $moduleInstellingen)
					<a class="list-group-item list-group-item-action" href="#instelling-{{$module}}">{{ucfirst($module)}}</a>
				@endforeach

				<a class="list-group-item list-group-item-action" href="#table-automatisch-inloggen">Automatisch inloggen</a>
				<a class="list-group-item list-group-item-action" href="#table-sessiebeheer">Sessiebeheer</a>
			</div>
		</div>
	</div>
@endsection
