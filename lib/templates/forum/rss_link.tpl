{if LoginModel::getAccount()->hasPrivateToken()}
	<a class="float-right" href="{LoginModel::getAccount()->getRssLink()}"{if LoginModel::mag('P_LOGGED_IN')} title="Persoonlijke RSS-feed forum&#013;Nieuwe aanvragen kan op je profiel"{/if}>
{else}
	<a class="float-right" href="/profiel/{LoginModel::getUid()}#tokenaanvragen" title="Persoonlijke RSS-feed aanvragen">
{/if}
{icon get="feed"}</a>