<div id="zijbalk_agenda">
	<div class="zijbalk-kopje">
		<a href="/agenda" title="Agenda">Agenda</a>
	</div>
	@foreach($items as $item)
		<div class="item">
			@if($item->getUrl())
				<a href="{{$item->getUrl()}}" title="{{$item->getBeschrijving()}}">
					<span class="zijbalk-moment">{{zijbalk_date_format($item->getBeginMoment())}}</span>
					&nbsp;{{$item->getTitel()}}
				</a>
			@else
				<a title="{{$item->getBeschrijving()}}"
					 href="/agenda/{{strftime("%Y/%m", $item->getBeginMoment())}}#dag-{{strftime("%Y-%m-%d", $item->getBeginMoment())}}">
					<span class="zijbalk-moment">{{zijbalk_date_format($item->getBeginMoment())}}</span>
					&nbsp;{{$item->getTitel()}}
				</a>
			@endif
		</div>
	@endforeach
</div>

