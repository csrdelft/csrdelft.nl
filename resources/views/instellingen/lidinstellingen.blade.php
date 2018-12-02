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
					@php(list($titel, $type, $opties, $default, $beschrijving) = $instelling)
					@php($keuze = isset($instellingen[$module][$id]) ? $instellingen[$module][$id] : $default)
					<div class="form-group row">
						<label class="col-md-3 col-form-label" for="inst_{{$module}}_{{$id}}">{!! $titel !!}</label>

						@if($type === CsrDelft\Orm\Entity\T::Enumeration)
							@if(count($opties) > 8)
								<div class="col-md-9">
									<select name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" class="form-control change-opslaan"
													data-href="/instellingen/update/{{$module}}/{{$id}}">
										@foreach($opties as $optie)
											<option value="{{$optie}}" @if($optie === $keuze) selected @endif>{{ucfirst($optie)}}</option>
										@endforeach
									</select>
								</div>
							@else
								{{-- Verticaal op xs --}}
								<div class="d-block d-sm-none w-100">
									<div class="btn-group-vertical btn-group-toggle col-md-9" data-buttons="radio">
										@foreach($opties as $optieId => $optie)
											<a class="post noanim instellingKnop btn btn-secondary @if($optie === $keuze) active @endif"
												 href="/instellingen/update/{{$module}}/{{$id}}/{{$optieId}}">{{ucfirst($optie)}}</a>
										@endforeach
									</div>
								</div>
								{{-- Horizontaal op alle andere --}}
								<div class="d-none d-sm-block">
									<div class="btn-group btn-group-toggle col-md-9" data-buttons="radio">
										@foreach($opties as $optieId => $optie)
											@php($optieId = is_int($optieId) ? $optie : $optieId)
											<a class="post noanim instellingKnop btn btn-secondary @if($optieId === $keuze) active @endif"
												 href="/instellingen/update/{{$module}}/{{$id}}/{{$optieId}}">{{ucfirst($optie)}}</a>
										@endforeach
									</div>
								</div>
							@endif
						@elseif($type === CsrDelft\Orm\Entity\T::String)
							<div class="col-md-9">
								<input type="text" name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" value="{{$keuze}}"
											 data-href="/instellingen/update/{{$module}}/{{$id}}"
											 class="form-control change-opslaan" minlength="{{$opties[0]}}" maxlength="{{$opties[1]}}"/>
							</div>
						@elseif($type === CsrDelft\Orm\Entity\T::Integer)
							<div class="col-md-9">
								<input type="number" name="{{$module}}_{{$id}}" id="inst_{{$module}}_{{$id}}" value="{{$keuze}}"
											 data-href="/instellingen/update/{{$module}}/{{$id}}"
											 class="form-control change-opslaan" data-href="/instellingen/update/{{$module}}/{{$id}}"
											 min="{{$opties[0]}}" max="{{$opties[1]}}"/>
							</div>
						@else
							<div class="col-md-9 bg-danger">Voor dit type bestaat geen optie.</div>
						@endif
						<small class="col-md-9 offset-md-3 form-text text-muted">
							@php($default = isset($opties[$default]) ? $opties[$default] : $default)
							{{$beschrijving}} Standaard waarde: "{{ucfirst($default)}}".
						</small>
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
