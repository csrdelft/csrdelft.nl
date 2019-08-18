<div class="menu-item-row row">
	<div class="col">
<span>
	@if($root->tekst == CsrDelft\model\security\LoginModel::getUid())
		Favorieten
	@else
		{{$root->tekst}}
	@endif
</span>
		@can(P_ADMIN)
			<span class="text-muted">({{$root->item_id}})</span>
		@endcan
	</div>
	<div class="col-auto">
		<a href="/menubeheer/toevoegen/{{$root->item_id}}" class="btn btn-sm post popup" title="Menu-item toevoegen">
			@icon('add')
		</a>
		@can(P_ADMIN)
			<a href="/menubeheer/bewerken/{{$root->item_id}}" class="btn btn-sm post popup" title="Dit menu bewerken">
				@icon('bewerken')
			</a>
		@endcan
	</div>
</div>
