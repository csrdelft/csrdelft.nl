<ul id="forumReageren">
	{foreach from=$reageren item=lid}
		{if $lid->uid != LoginModel::getUid()}
			<li class="reagerenLid" title="{$lid->uid|csrnaam:'user':'plain'} is een reactie aan het schrijven">{icon get=comment_edit} {$lid->uid|csrnaam:'user'}</li>
		{/if}
	{/foreach}
</ul>