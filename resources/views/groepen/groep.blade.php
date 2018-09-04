<a name="{{$groep->id}}"></a>
<div id="groep-{{$groep->id}}" class="bb-groep @if($geschiedenis) state-geschiedenis @endif @if($bbAan)bb-block @endif">
	<div id="groep-samenvatting-{{$groep->id}}" class="groep-samenvatting">
		@if($groep->mag(\CsrDelft\model\entity\security\AccessAction::Wijzigen))
			<div class="float-right">
				<a class="btn" href="{!!$groep->getUrl()!!}wijzigen" title="Wijzig {{$groep->naam}}">
					<span class="fa fa-pencil"></span>
				</a>
			</div>
		@endif

		<h3>
			@section('title')
				{{$groep->naam}}
			@show
			@yield('location', '')
			<span class="groep-id-hint">(<a href="{!! $groep->getUrl() !!}">#{{$groep->id}}</a>)</span>
			@yield('temporal', '')
		</h3>
		{!! \CsrDelft\view\bbcode\CsrBB::parse($groep->samenvatting) !!}
		@unless(empty($groep->omschrijving))
			<div class="clear">&nbsp;</div>
			<a id="groep-omschrijving-{{$groep->id}}" class="post noanim" href="{!! $groep->getUrl() !!}omschrijving">
				Meer lezen »
			</a>
		@endunless
	</div>
	@php($leden = \CsrDelft\model\AbstractGroepenModel::getTabView($groep, $tab))
	{!! $leden->getHtml() !!}
	<div class="clear">&nbsp;</div>
</div>
