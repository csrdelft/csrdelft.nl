<div class="clear" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
	<a name="RSS"></a>
	{icon get="feed"}
{if LoginModel::getUid() == 'x999' OR LoginModel::getAccount()->hasPrivateToken()}
	<input type="text" value="{LoginModel::getAccount()->getRssLink()}" size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly />
{else}
	<a href="/profiel/{LoginModel::getUid()}#tokenaanvragen">Privé url aanvragen</a>
{/if}
</div>