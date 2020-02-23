@foreach($parent->getChildren() as $item)
	@if($item->tekst == 'Personal')
	@elseif($item->magBekijken())
		@if($item->hasChildren())
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="menu-{{$item->item_id}}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					{{$item->tekst}}
				</a>
				<ul class="dropdown-menu" aria-labelledby="menu-{{$item->item_id}}">
					@include('menu.sub_tree', ['parent' => $item, 'sub' => true])
				</ul>
			</li>
		@else
			<li class="nav-item">
				<a class="nav-link" href="{{$item->link}}" @if(startsWith($item->link, 'http')) target="_blank" @endif >{{$item->tekst}}</a>
			</li>
		@endif
	@endif
@endforeach
