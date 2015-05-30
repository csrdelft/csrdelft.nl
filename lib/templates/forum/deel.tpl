{getMelding()}

{$zoekform->view()}

{if isset($deel->forum_id) AND LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/beheren/{$deel->forum_id}" class="btn post popup" title="Deelforum beheren">{icon get="wrench_orange"} Beheren</a>
	</div>
{/if}

{include file='forum/head_buttons.tpl'}

<h1>{$deel->titel}{if !isset($deel->forum_id)}{include file='forum/rss_link.tpl'}{/if}</h1>

<table id="forumtabel">
	<thead>
		<tr>
			<th>Titel</th>
			<th colspan="2">Laatste wijziging</th>
		</tr>
	</thead>
	<tbody>

		{if !$deel->hasForumDraden()}
			<tr>
				<td colspan="3">Dit forum is nog leeg.</td>
			</tr>
		{/if}

		{foreach from=$deel->getForumDraden() item=draad}
			{include file='forum/draad_lijst.tpl'}
		{/foreach}

		{if $paging}
			<tr>
				<th colspan="3">
					{if isset($deel->forum_id)}
						{sliding_pager baseurl="/forum/deel/"|cat:$deel->forum_id|cat:"/"
							pagecount=ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) curpage=ForumDradenModel::instance()->getHuidigePagina()
							separator=" &nbsp;" show_prev_next=true}
					{else}
						{sliding_pager baseurl="/forum/recent/" url_append=$belangrijk
							pagecount=ForumDradenModel::instance()->getHuidigePagina() curpage=ForumDradenModel::instance()->getHuidigePagina()
							separator=" &nbsp;"}
						&nbsp;<a href="/forum/recent/{ForumDradenModel::instance()->getAantalPaginas(null)}{$belangrijk}">verder terug</a>
					{/if}
				</th>
			</tr>
		{/if}

		<tr>
			<td colspan="3">
				<div class="forumdeel-omschrijving">
					<div class="breadcrumbs float-right">{$breadcrumbs}</div>
					<h2>{$deel->titel}</h2>
					{$deel->omschrijving}

					{if !isset($deel->forum_id) AND LoginModel::mag('P_LOGGED_IN')}
						<div class="grafiek">
							{include file='forum/stats_grafiek.tpl'}
						</div>
					{/if}
				</div>
			</td>
		</tr>

		{if $deel->magPosten()}
			<tr>
				<td colspan="3">
					<br />
					<table>
						{include file='forum/post_form.tpl' draad=null}
					</table>
				</td>
			</tr>
		{/if}

	</tbody>
</table>