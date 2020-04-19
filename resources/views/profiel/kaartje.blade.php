<div class="card visitekaartje flex-row">
	<div class="card-body @if($profiel->isJarig()) jarig @endif ">
		@if (\CsrDelft\repository\security\AccountRepository::existsUid($profiel->uid) AND CsrDelft\model\security\LoginModel::instance()->maySuTo($profiel->getAccount()))
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
		@php($bestuurslid = CsrDelft\model\groepen\leden\BestuursLedenModel::instance()->find('uid = ?', array($profiel->uid), null, null, 1)->fetch())
		@if($bestuurslid)
			@php($bestuur = CsrDelft\model\groepen\BesturenModel::instance()->get($bestuurslid->groep_id))
			<p><a
					href="{{$bestuur->getUrl()}}">{{CsrDelft\model\entity\groepen\GroepStatus::getChar($bestuur->status)}} {{$bestuurslid->opmerking}}</a>
			</p>
		@endif

		@foreach (CsrDelft\model\groepen\leden\CommissieLedenModel::instance()->find('uid = ?', array($profiel->uid), null, 'lid_sinds DESC') as $commissielid)
			@php($commissie = CsrDelft\model\groepen\CommissiesModel::instance()->get($commissielid->groep_id))
			@if ($commissie->status === CsrDelft\model\entity\groepen\GroepStatus::HT)
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
