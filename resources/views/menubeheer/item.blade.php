<li id="menu-item-{{$item->item_id}}" parentid="{{$item->parent_id}}" class="menu-item">
	@if($item->children)
		<button class="btn btn-sm caret"
						onclick="$(this).parent().children('ul').slideToggle();$(this).children('span.fa').toggleClass('fa-caret-right fa-caret-down');">
			<span class="fa fa-caret-down fa-fw"></span>
		</button>
	@endif
	<div class="menu-item-row row">
		<div class="col">
			<span class="text-muted">{{$item->volgorde}}</span>
			<span>{{$item->tekst}}</span>
			[<a href="{{$item->link}}">{{$item->link}}</a>]
			@can(P_ADMIN)
				@if($item->item_id !== null && $item->item_id > 0)
					<span class="text-muted">({{$item->item_id}})</span>
				@endif
			@endcan
		</div>
		<div class="col-auto">
			@if ($item->rechten_bekijken !== P_PUBLIC && $item->rechten_bekijken != \CsrDelft\model\security\LoginModel::getUid())
				<button class="btn btn-sm"
								disabled>@icon('group_key', null, 'Rechten bekijken: &#013; ' . $item->rechten_bekijken)</button>
			@endif
			@if ($item->item_id !== null && $item->item_id > 0)
				<a href="/menubeheer/verwijderen/{{$item->item_id}}" class="btn btn-sm post confirm ReloadPage"
					 title="Dit menu-item definitief verwijderen">
					@icon('cross')
				</a>
				<a href="/menubeheer/zichtbaar/{{$item->item_id}}" class="btn btn-sm post ReloadPage"
					 @if($item->zichtbaar)
					 title="Menu-item is nu zichtbaar"
					 @else
					 title="Menu-item is nu onzichtbaar"
					@endif
				>
					@if ($item->zichtbaar)
						@icon('eye')
					@else
						@icon('shading')
					@endif
				</a>
				@can(P_ADMIN)
					<a href="/menubeheer/toevoegen/{{$item->item_id}}" class="btn btn-sm post popup"
						 title="Sub-menu-item toevoegen">
						@icon('add')
					</a>
				@endcan
				<a href="/menubeheer/bewerken/{{$item->item_id}}" class="btn btn-sm post popup"
					 title="Dit menu-item bewerken">@icon('bewerken')</a>
			@else
				<button class="btn btn-sm" disabled>
					@icon('wand', null, 'Automatisch menu item')
				</button>
			@endif
		</div>
	</div>
	@if ($item->children)
		<ul class="menubeheer-tree">
			@foreach ($item->children as $child)
				@include('menubeheer.item', ['item' => $child])
			@endforeach
		</ul>
	@endif
</li>
