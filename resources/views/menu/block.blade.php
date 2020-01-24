<div class="zijbalk-kopje @if($root->tekst == 'Sponsors') ads @endif">
	@if ($root->link)
		<a href="{{$root->link}}">{{$root->tekst}}</a>
	@else
		<a href="#0">{{$root->tekst}}</a>
	@endif
</div>
@if($root->children)
@foreach ($root->children as $item)
	@if($item->magBekijken())
		<div class="item @if($item->active) active @endif @if($root->tekst == 'Sponsors') ads @endif">
			&raquo;
			<a href="{{$item->link}}" title="{{$item->tekst}}"
				 @if($item->isOngelezen()) class="{{lid_instelling('forum', 'ongelezenWeergave')}}" @endif>
				{{$item->tekst}}
			</a>
		</div>
	@endif
@endforeach
@endif
