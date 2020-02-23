<?php /** @var \CsrDelft\entity\profiel\Profiel $profiel */ ?>
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
					<span class="datum">{{date('d-m', $profiel->gebdatum->getTimestamp())}}</span>
				</div>
			@endforeach
			<div class="clear"></div>
		</div>
	@else
		@foreach($verjaardagen as $profiel)
			<div class="item">{{date('d-m', $profiel->gebdatum->getTimestamp())}}
				<span @if($profiel->isJarig()) class="cursief" @endif >{!! $profiel->getLink('civitas') !!}</span>
			</div>
		@endforeach
	@endif

</div>

