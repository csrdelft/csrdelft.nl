{toegang P_LOGGED_IN}
<ul class="forum-reageren">
	{foreach from=$reageren item=react}
		<li class="reagerenLid" title="{CsrDelft\model\ProfielModel::getNaam($react->uid, 'user')} is een reactie aan het schrijven">{icon get=comment_edit} {CsrDelft\model\ProfielModel::getNaam($react->uid, 'user')}</li>
	{foreachelse}
		<li class="reagerenLid"><br/></li>
	{/foreach}
</ul>
{/toegang}
