{if LoginModel::instance()->getLid()->hasPrivateToken()}
	<a class="float-right" href="{LoginModel::instance()->getLid()->getRssLink()}" title="{if LoginModel::mag('P_LOGGED_IN')}Persoonlijke {/if} RSS-feed forum">
{else}
	<a class="float-right" href="/communicatie/profiel/{$profiel->getUid()}#tokenaanvragen" title="Persoonlijke RSS-feed aanvragen">
{/if}
{icon get="feed"}</a>