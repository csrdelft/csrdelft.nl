<?php
/**
 * @var \CsrDelft\entity\profiel\Profiel $profiel
 */
?>
<div class="card visitekaartje flex-row">
	<div class="card-body @if($profiel->isJarig()) jarig @endif ">
		@if ($profiel->account && \CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\model\security\LoginModel::class)->maySuTo($profiel->account))
			<div class="float-right">
				<a href="/su/{{$profiel->uid}}" title="Su naar dit lid">{{$profiel->uid}}</a>
			</div>
		@endif
		<p class="naam">
			<a href="/profiel/{{$profiel->uid}}" class="lidLink {{$profiel->status}}">
				{{$profiel->getNaam('volledig')}} &nbsp; {{CsrDelft\model\entity\LidStatus::getChar($profiel->status)}}
			</a>
		</p>
		<p>
			{{$profiel->lidjaar}}
			@if ($profiel->getVerticale())
				{{$profiel->getVerticale()->naam}}
			@endif
		</p>
		@php($bestuurslid = \CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\leden\BestuursLedenRepository::class)->find('uid = ?', array($profiel->uid), null, null, 1)->fetch())
		@if($bestuurslid)
			@php($bestuur = \CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\BesturenRepository::class)->get($bestuurslid->groep_id))
			<p><a
					href="{{$bestuur->getUrl()}}">{{\CsrDelft\entity\groepen\GroepStatus::getChar($bestuur->status)}} {{$bestuurslid->opmerking}}</a>
			</p>
		@endif

		@foreach (\CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\leden\CommissieLedenRepository::class)->find('uid = ?', array($profiel->uid), null, 'lid_sinds DESC') as $commissielid)
			@php($commissie = \CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\CommissiesRepository::class)->get($commissielid->groep_id))
			@if ($commissie->status === CsrDelft\entity\groepen\GroepStatus::HT)
				<p>
					@if (!empty($commissielid->opmerking))
						{{$commissielid->opmerking}} <br/>
					@endif
					<a href="{{$commissie->getUrl()}}">{{$commissie->naam}}</a></p>
			@endif
		@endforeach

	</div>
	{!! $profiel->getPasfotoTag('') !!}
</div>
