<ul id="forumReageren">
	{foreach from=$reageren item=lid}
		<li class="reagerenLid" title="{$lid->uid|csrnaam:'user':'plain'} is een reactie aan het schrijven">{icon get=comment_edit} {$lid->uid|csrnaam:'user'}</li>
	{foreachelse}
		<li class="reagerenLid"><br/></li>
	{/foreach}
</ul>