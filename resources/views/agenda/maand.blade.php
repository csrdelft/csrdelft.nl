@extends('layout')

@section('titel')
	Maandoverzicht voor {{strftime('%B %Y', strtotime($jaar . '-' . $maand . '-01'))}}
@endsection

@section('breadcrumbs')
	{!! \CsrDelft\view\agenda\AgendaBreadcrumbs::getBreadcrumbs2($maand, $jaar) !!}
@endsection

@section('content')
	<div id="agenda"
			 data-maand="{{$maand}}"
			 data-jaar="{{$jaar}}"
			 data-weergave="{{lid_instelling('agenda', 'weergave')}}"
			 data-creator="{{$creator ? "true" : "false"}}"></div>
	<div id="ICAL" class="input-group mt-2" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
		<div class="input-group-prepend">
			<label class="input-group-text" for="ical-link"><img src="/images/ical.gif" alt="ICAL"/></label>
		</div>
		@if(\CsrDelft\service\security\LoginService::getUid() == \CsrDelft\service\security\LoginService::UID_EXTERN OR \CsrDelft\service\security\LoginService::getAccount()->hasPrivateToken())
			<input class="form-control" type="text" id="ical-link"
						 value="{{\CsrDelft\service\security\LoginService::getAccount()->getICalLink()}}"
						 size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly/>
		@else
			<a class="input-group-text" href="/profiel/{{\CsrDelft\service\security\LoginService::getUid()}}#tokenaanvragen">
				Privé url aanvragen
			</a>
		@endif
	</div>
@endsection
