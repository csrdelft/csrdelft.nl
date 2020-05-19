<div class="rss-link input-group mt-4" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
	<div class="input-group-prepend">
		<span class="input-group-text">@icon('feed')</span>
	</div>
	@if(\CsrDelft\service\security\LoginService::getUid() == \CsrDelft\service\security\LoginService::UID_EXTERN || \CsrDelft\service\security\LoginService::getAccount()->hasPrivateToken())
		<input type="text" class="form-control" value="{{\CsrDelft\service\security\LoginService::getAccount()->getRssLink()}}"
					 size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly title="RSS Link"/>
	@else
		<span class="input-group-text">
			<a href="/profiel/{{\CsrDelft\service\security\LoginService::getUid()}}#tokenaanvragen">Privé url aanvragen</a>
		</span>
	@endif
</div>
