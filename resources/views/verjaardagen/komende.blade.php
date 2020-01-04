<div id="zijbalk_verjaardagen">
	<div class="zijbalk-kopje">
		@can(P_LEDEN_READ)
			<a href="/leden/verjaardagen">Verjaardagen</a>
		@else
			Verjaardagen
		@endcan
	</div>

	@if($toonpasfotos)
		<div class="item" id="komende_pasfotos">
			@foreach($verjaardagen as $profiel)
				<div class="verjaardag @if($profiel->isJarig()) cursief @endif ">
					{!! $profiel->getLink('pasfoto') !!}
					<span class="datum">{{date('d-m', strtotime($profiel->gebdatum))}}</span>
				</div>
			@endforeach
			<div class="clear"></div>
		</div>
	@else
		@foreach($verjaardagen as $profiel)
			<div class="item">{{date('d-m', strtotime($profiel->gebdatum))}}
				<span @if($profiel->isJarig()) class="cursief" @endif >{!! $profiel->getLink('civitas') !!}</span>
			</div>
		@endforeach
	@endif

</div>

