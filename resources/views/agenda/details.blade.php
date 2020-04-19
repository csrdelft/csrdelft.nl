<?php
/**
 * @var \CsrDelft\entity\agenda\Agendeerbaar $item
 * @var \CsrDelft\entity\agenda\AgendaItem $item
 * @var \CsrDelft\entity\profiel\Profiel $item
 * @var \CsrDelft\model\entity\maalcie\CorveeTaak $item
 */
?>
<div class="card agenda-card">
	<div class="card-header">
		<div class="row no-gutters align-items-center">
			<div class="col">
				<h5 class="card-title mb-0">
					@if($item instanceof \CsrDelft\entity\profiel\Profiel)
						@icon('verjaardag') {!! $item->getLink() !!}
					@elseif($item instanceof \CsrDelft\entity\maalcie\Maaltijd)
						<img src="/images/maalcie/cutlery.png" width="16" height="16" alt="cutlery" class="icon"/>
						<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
					@elseif($item instanceof \CsrDelft\model\entity\maalcie\CorveeTaak)
						@if(stristr($item->getCorveeFunctie()->naam, "klus"))
							<img src="/images/maalcie/drill.png" width="16" height="16" alt="drill" class="icon"/>
						@else
							@icon('paintcan')
						@endif
						<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
					@elseif($item instanceof \CsrDelft\entity\agenda\Agendeerbaar)
						@if($item->getUrl())
							<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
						@else
							{{$item->getTitel()}}
						@endif
					@endif
				</h5>
			</div>
			<div class="col-auto">
				<div class="btn-group btn-group-sm">
					@if($verborgen)
						<a href="/agenda/verbergen/{{$item->getUUID()}}" class="btn beheren post" title="Toon dit agenda item in ical">
							@icon('shading')
						</a>
					@else
						<a href="/agenda/verbergen/{{$item->getUUID()}}" class="btn beheren post" title="Verberg dit agenda item in ical">
							@icon('eye')
						</a>
					@endif

					<a href="/agenda/export/{{$item->getUUID()}}.ics" class="btn" title="Exporteer dit agenda item">
						@icon('date_go')
					</a>

					@if($item instanceof \CsrDelft\model\entity\groepen\AbstractGroep && $item->mag(\CsrDelft\model\entity\security\AccessAction::Wijzigen))
						<a href="{{$item->getUrl()}}/wijzigen" class="beheren btn" title="Wijzig {{$item->naam}}">
							@icon('bewerken')
						</a>
					@elseif($item instanceof \CsrDelft\entity\agenda\AgendaItem && $item->magBeheren())
						<a href="/agenda/bewerken/{{$item->item_id}}" class="btn beheren post popup"
							 title="Dit agenda-item bewerken">
							@icon('bewerken')
						</a>
						<a href="/agenda/verwijderen/{{$item->item_id}}" class="btn beheren post confirm ReloadAgenda"
							 title="Dit agenda-item definitief verwijderen">
							@icon('bin')
						</a>
					@endif
					<a href="#" class="btn close" title="Sluiten">
						<i class="fa fa-times"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		@if($item instanceof \CsrDelft\entity\maalcie\Maaltijd)
			<div class="tijd">
				{{$item->getBeginMoment()->format('H:i')}} - {{$item->getEindMoment()->format('H:i')}}
			</div>
		@elseif($item instanceof \CsrDelft\entity\agenda\Agendeerbaar)
			@if(!$item->isHeledag())
				<p>
					{{strftime("%R", $item->getBeginMoment())}}
					@if(!preg_match('/(00:00|23:59)/', strftime("%R", $item->getEindMoment())))
						- {{strftime("%R", $item->getEindMoment())}}
					@endif
				</p>
			@endif
		@endif
		@if($item->getBeschrijving())
			<p>{{$item->getBeschrijving()}}</p>
		@endif
		@if($item->getLocatie())
			<p>{!! bbcode('[kaart h=200]' . $item->getLocatie() . '[/kaart]') !!}</p>
		@endif
		@if($item instanceof \CsrDelft\entity\agenda\AgendaItem && $item->rechten_bekijken != P_LOGGED_IN)
			<span class="text-muted small">Zichtbaar voor: {{$item->rechten_bekijken}}</span>
		@endif
	</div>
</div>
