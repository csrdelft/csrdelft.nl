@extends('layout')

@section('titel')
	Maandoverzicht voor {{strftime('%B %Y', strtotime($jaar . '-' . $maand . '-01'))}}
@endsection

@section('breadcrumbs')
	{!! \CsrDelft\view\agenda\AgendaBreadcrumbs::getBreadcrumbs2($maand, $jaar) !!}
@endsection

@section('content')
	<div id="agenda" data-maand="{{$maand}}" data-jaar="{{$jaar}}"></div>
	<div id="ICAL" class="input-group mt-2" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
		<div class="input-group-prepend">
			<label class="input-group-text" for="ical-link"><img src="/images/ical.gif" alt="ICAL"/></label>
		</div>
		@if(CsrDelft\model\security\LoginModel::getUid() == 'x999' OR CsrDelft\model\security\LoginModel::getAccount()->hasPrivateToken())
			<input class="form-control" type="text" id="ical-link"
						 value="{{CsrDelft\model\security\LoginModel::getAccount()->getICalLink()}}"
						 size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly/>
		@else
			<a class="input-group-text" href="/profiel/{{CsrDelft\model\security\LoginModel::getUid()}}#tokenaanvragen">
				Privé url aanvragen
			</a>
		@endif
	</div>
@endsection
