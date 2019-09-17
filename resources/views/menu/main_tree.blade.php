@foreach($parent->getChildren() as $item)
	@if($item->tekst == 'Personal')
		@include('menu.personal', ['parent' => $item])
	@elseif($item->magBekijken())
		@if($item->hasChildren())
			<li class="has-children">
				<a href="#menu">{{$item->tekst}}</a>
				<ul class="is-hidden">
					<li class="go-back"><a href="#menu">{{$item->tekst}}</a></li>
					@include('menu.main_tree', ['parent' => $item])
				</ul>
			</li>
		@else
			<li><a href="{{$item->link}}" @if(startsWith($item->link, 'http')) target="_blank" @endif >{{$item->tekst}}</a>
			</li>
		@endif
	@endif
@endforeach
