<?php
/**
 * @var \CsrDelft\model\entity\agenda\Agendeerbaar $item
 * @var \CsrDelft\model\entity\agenda\AgendaItem $item
 * @var \CsrDelft\model\entity\profiel\Profiel $item
 * @var \CsrDelft\model\entity\maalcie\CorveeTaak $item
 */
?>
@php($verborgen = \CsrDelft\model\agenda\AgendaVerbergenModel::instance()->isVerborgen($item))
<li id="item-{{str_replace('@', '-', str_replace('.', '-', $item->getUUID()))}}"
		@if($verborgen) class="offtopic" @endif
		title="{{$item->getBeschrijving()}}"
		parentid="items-{{strftime("%Y-%m-%d", $item->getBeginMoment())}}">
	@if($verborgen)
		<a href="/agenda/verbergen/{{$item->getUUID()}}" class="beheren post" title="Toon dit agenda item">
			@icon('shading')
		</a>
	@else
		<a href="/agenda/verbergen/{{$item->getUUID()}}" class="beheren post" title="Verberg dit agenda item">
			@icon('eye')
		</a>
	@endif

	@if($item instanceof \CsrDelft\model\entity\groepen\AbstractGroep && $item->mag(\CsrDelft\model\entity\security\AccessAction::Wijzigen))
		<a href="{{$item->getUrl()}}wijzigen" class="beheren" title="Wijzig {{$item->naam}}">
			@icon('bewerken')
		</a>
	@elseif($item instanceof \CsrDelft\model\entity\agenda\AgendaItem && $item->magBeheren())
		<a href="/agenda/bewerken/{{$item->item_id}}" class="beheren post popup" title="Dit agenda-item bewerken">
			@icon('bewerken')
		</a>
		<a href="/agenda/verwijderen/{{$item->item_id}}" class="beheren post confirm"
			 title="Dit agenda-item definitief verwijderen">
			@icon('verwijderen')
		</a>
	@endif
	@if($item instanceof \CsrDelft\model\entity\profiel\Profiel)
		@icon('verjaardag') {!! $item->getLink() !!}
	@elseif($item instanceof \CsrDelft\model\entity\maalcie\Maaltijd)
		<img src="/images/maalcie/cutlery.png" width="16" height="16" alt="cutlery" class="icon"/>
		<div class="tijd">
			{{strftime("%R", $item->getBeginMoment())}} - {{strftime("%R", $item->getEindMoment())}}
		</div>
		<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
	@elseif($item instanceof \CsrDelft\model\entity\maalcie\CorveeTaak)
		@if(stristr($item->getCorveeFunctie()->naam, "klus"))
			<img src="/images/maalcie/drill.png" width="16" height="16" alt="drill" class="icon"/>
		@else
			@icon('paintcan')
		@endif
		<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
	@elseif($item instanceof \CsrDelft\model\entity\agenda\Agendeerbaar)
		@if(!$item->isHeledag())
			<div class="tijd">
				{{strftime("%R", $item->getBeginMoment())}}
				@if(!preg_match('/(00:00|23:59):[0-9]{2}$/', $item->eind_moment))
					- {{strftime("%R", $item->getEindMoment())}}
				@endif
			</div>
		@endif
		<div class="hoverIntent">
			@if($item->getUrl())
				<a href="{{$item->getUrl()}}">{{$item->getTitel()}}</a>
			@else
				{{$item->getTitel()}}
			@endif
			@if($item->getLocatie())
				<a
					href="https://maps.google.nl/maps?q={{htmlspecialchars($item->getLocatie())}}">@icon('map', null, 'Kaart')</a>
				<div class="hoverIntentContent">
					{!! bbcode('[kaart]' . $item->getLocatie() . '[/kaart]') !!}
				</div>
			@endif
		</div>
	@endif
</li>
