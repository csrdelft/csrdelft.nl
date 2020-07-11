<?php
/**
 * @var \CsrDelft\entity\profiel\Profiel $profiel
 */
?>
<div class="card visitekaartje flex-row">
	<div class="card-body @if($profiel->isJarig()) jarig @endif ">
		@if ($profiel->account && \CsrDelft\common\ContainerFacade::getContainer()->get(\CsrDelft\service\security\SuService::class)->maySuTo($profiel->account))
			<div class="float-right">
				<a href="?_switch_user={{$profiel->uid}}" title="Su naar dit lid">{{$profiel->uid}}</a>
			</div>
		@endif
		<p class="naam">
			<a href="/profiel/{{$profiel->uid}}" class="lidLink {{$profiel->status}}">
				{{$profiel->getNaam('volledig')}} &nbsp; {{CsrDelft\model\entity\LidStatus::from($profiel->status)->getChar()}}
			</a>
		</p>
		<p>
			{{$profiel->lidjaar}}
			@if ($profiel->getVerticale())
				{{$profiel->getVerticale()->naam}}
			@endif
		</p>
		@php($bestuurslid = \CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\leden\BestuursLedenRepository::class)->findOneBy(['uid' => $profiel->uid]))
		@if($bestuurslid)
			<p><a
					href="{{$bestuurslid->groep->getUrl()}}">{{$bestuurslid->groep->status->getDescription()}} {{$bestuurslid->opmerking}}</a>
			</p>
		@endif

		@foreach (\CsrDelft\common\ContainerFacade::getContainer()->get(CsrDelft\repository\groepen\leden\CommissieLedenRepository::class)->findBy(['uid' => $profiel->uid], ['lid_sinds' => 'DESC']) as $commissielid)
			@if ($commissielid->groep->status === \CsrDelft\entity\groepen\enum\GroepStatus::HT())
				<p>
					@if (!empty($commissielid->opmerking))
						{{$commissielid->opmerking}} <br/>
					@endif
					<a href="{{$commissielid->groep->getUrl()}}">{{$commissielid->groep->naam}}</a></p>
			@endif
		@endforeach

	</div>
	{!! $profiel->getPasfotoTag('') !!}
</div>
