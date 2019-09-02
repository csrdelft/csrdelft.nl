@extends('layout')

@section('titel')
	Maandoverzicht voor {{strftime('%B %Y', strtotime($jaar . '-' . $maand . '-01'))}}
@endsection

@section('breadcrumbs')
	{!! \CsrDelft\view\agenda\AgendaBreadcrumbs::getBreadcrumbs2($maand, $jaar) !!}
@endsection

@section('navlinks')
	<div class="maandnavigatie">
		<a class="btn float-left" href="{{$urlVorige}}">&laquo; {{$prevMaand}}</a>
		<a class="btn float-right" href="{{$urlVolgende}}">{{$nextMaand}} &raquo;</a>
		<h1>{{strftime("%B %Y", $datum)}}</h1>
	</div>
@endsection

@section('content')
	@yield('navlinks')
	<div id="agenda"></div>
	<table class="agenda" id="maand">
		<tr>
			<th class="weeknr"></th>
			<th>Zondag</th>
			<th>Maandag</th>
			<th>Dinsdag</th>
			<th>Woensdag</th>
			<th>Donderdag</th>
			<th>Vrijdag</th>
			<th>Zaterdag</th>
		</tr>
		@foreach($weken as $weeknr => $dagen)
			@foreach($dagen as $dagnr => $dag)
				@if($loop->first)
					<tr @if(strftime('%U', $dag['datum']) == strftime('%U')) id="dezeweek" @endif >
						<th>{{$weeknr}}</th>
						@endif
						<td id="dag-{{strftime("%Y-%m-%d", $dag['datum'])}}"
								class="dag @if(strftime('%m', $dag['datum']) != strftime('%m', $datum)) anderemaand @endif @if(date('d-m', $dag['datum'])==date('d-m')) vandaag @endif ">
							<div class="meta">
								@can(P_AGENDA_ADD)
									<a href="/agenda/toevoegen/{{strftime("%Y-%m-%d", $dag['datum']) }}" class="beheren post popup"
										 title="Agenda-item toevoegen">@icon('add')</a>
								@endcan
								{{$dagnr}}
							</div>
							<ul id="items-{{strftime("%Y-%m-%d", $dag['datum'])}}" class="items">
								@foreach($dag['items'] as $item)
									@include('agenda.maand_item', ['item' => $item])
								@endforeach
							</ul>
						</td>
						@endforeach
					</tr>
					@endforeach
	</table>
	@yield('navlinks')
	<div id="ICAL" class="input-group" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
		<div class="input-group-prepend">
			<label class="input-group-text" for="ical-link"><img src="/images/ical.gif" alt="ICAL"/></label>
		</div>
		@if(CsrDelft\model\security\LoginModel::getUid() == 'x999' OR CsrDelft\model\security\LoginModel::getAccount()->hasPrivateToken())
			<input class="form-control" type="text" id="ical-link"
						 value="{{CsrDelft\model\security\LoginModel::getAccount()->getICalLink()}}"
						 size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly/>
		@else
			<a class="input-group-text" href="/profiel/{{CsrDelft\model\security\LoginModel::getUid()}}#tokenaanvragen">Privé
				url
				aanvragen</a>
		@endif
	</div>
@endsection
