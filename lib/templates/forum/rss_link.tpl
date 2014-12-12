{if LoginModel::instance()->getLid()->hasPrivateToken()}
	<a class="float-right" href="{LoginModel::instance()->getLid()->getRssLink()}" title="{if LoginModel::mag('P_LOGGED_IN')}Persoonlijke {/if} RSS-feed forum&#013;Nieuwe aanvragen kan op je profiel">
{else}
	<a class="float-right" href="/profiel/{LoginModel::getUid()}#tokenaanvragen" title="Persoonlijke RSS-feed aanvragen">
{/if}
{icon get="feed"}</a>