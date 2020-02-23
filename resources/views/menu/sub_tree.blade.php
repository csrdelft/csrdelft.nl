@foreach($parent->getChildren() as $item)
	@if($item->magBekijken())
		@if($item->hasChildren())
			<li class="dropdown-submenu @if(isset($dropleft)) dropleft @endif ">
				<a class="dropdown-item dropdown-toggle" href="#" id="menu-{{$item->item_id}}">
					{{$item->tekst}}
				</a>
				<ul class="dropdown-menu" aria-labelledby="menu-{{$item->item_id}}">
					@include('menu.sub_tree', ['parent' => $item])
				</ul>
			</li>
		@else
			<li>
				<a class="dropdown-item" href="{{$item->link}}"
					 @if(startsWith($item->link, 'http')) target="_blank" @endif >{{$item->tekst}}</a>
			</li>
		@endif
	@endif
@endforeach
