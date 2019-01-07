<div class="rss-link input-group mt-4" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
	<div class="input-group-prepend">
		<span class="input-group-text">@icon('feed')</span>
	</div>
	@if(CsrDelft\model\security\LoginModel::getUid() == 'x999' || CsrDelft\model\security\LoginModel::getAccount()->hasPrivateToken())
		<input type="text" class="form-control" value="{{CsrDelft\model\security\LoginModel::getAccount()->getRssLink()}}"
					 size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly title="RSS Link"/>
	@else
		<span class="input-group-text">
			<a href="/profiel/{{CsrDelft\model\security\LoginModel::getUid()}}#tokenaanvragen">Privé url aanvragen</a>
		</span>
	@endif
</div>
