{getMelding()}

<div class="forum-header">
	<h1>{$deel->titel}</h1>

	{$zoekform->view()}

{toegang P_ADMIN}
{if isset($deel->forum_id)}
	<div class="forumheadbtn">
		<a href="/forum/beheren/{$deel->forum_id}" class="btn post popup"
			 title="Deelforum beheren">{icon get="wrench_orange"} Beheren</a>
	</div>
{/if}
{/toegang}

{include file='forum/head_buttons.tpl'}
</div>


<div class="forum-deel">
	<div class="header">Titel</div>
	<div class="header">Laatste wijziging</div>
	<div class="header"></div>

	{if !$deel->hasForumDraden()}
		<div>Dit forum is nog leeg.</div>
	{/if}

	{foreach from=$deel->getForumDraden() item=draad}
		{include file='forum/draad_lijst.tpl'}
	{/foreach}

	{if $paging}
		<div class="paging">
			{if isset($deel->forum_id)}
				{sliding_pager baseurl="/forum/deel/"|cat:$deel->forum_id|cat:"/"
				pagecount=CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) curpage=CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina()
				separator=" &nbsp;" show_prev_next=true}
			{else}
				{sliding_pager baseurl="/forum/recent/" url_append=$belangrijk
				pagecount=CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina() curpage=CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina()
				separator=" &nbsp;"}
				&nbsp;
				<a
					href="/forum/recent/{CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas(null)}{$belangrijk}">verder
					terug</a>
			{/if}
		</div>
	{/if}


	<div class="forumdeel-omschrijving">
		<div class="breadcrumbs float-right">{$breadcrumbs}</div>
		<h2>{$deel->titel}</h2>
		{$deel->omschrijving}

		{toegang P_LOGGED_IN}
		{if !isset($deel->forum_id)}
			Berichten per dag: (sleep om te zoomen)
			<div class="grafiek">
				{include file='forum/stats_grafiek.tpl'}
			</div>
		{/if}
		{/toegang}
	</div>


	{if $deel->magPosten()}
		{include file='forum/post_form.tpl' draad=null}
	{/if}
</div>

{include file='forum/rss_link.tpl'}
