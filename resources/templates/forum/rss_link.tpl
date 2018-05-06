<div class="clear" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
	<a name="RSS"></a>
	{icon get="feed"}
{if CsrDelft\model\security\LoginModel::getUid() == 'x999' OR CsrDelft\model\security\LoginModel::getAccount()->hasPrivateToken()}
	<input type="text" value="{CsrDelft\model\security\LoginModel::getAccount()->getRssLink()}" size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly />
{else}
	<a href="/profiel/{CsrDelft\model\security\LoginModel::getUid()}#tokenaanvragen">Privé url aanvragen</a>
{/if}
</div>