{getMelding()}

{$zoekform->view()}

{if isset($deel->forum_id) AND LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/beheren/{$deel->forum_id}" class="knop post modal" title="Deelforum beheren">{icon get="wrench_orange"} Beheren</a>
	</div>
{/if}

{include file='MVC/forum/head_buttons.tpl'}

{capture name='navlinks'}
	<div class="breadcrumbs">
		<a href="/forum" class="forumGrootlink">Forum</a>
		{if $categorien}
			&raquo;
			<select name="forum_id" onchange="document.location.href = '/forum/' + this.value;">
				<option value="recent"{if 0 === $deel->forum_id} selected="selected"{/if}>Recent gewijzigd</option>
				{foreach from=$categorien item=cat}
					<optgroup label="{$cat->titel}">
						{foreach from=$cat->getForumDelen() item=newDeel}
							<option value="deel/{$newDeel->forum_id}"{if $newDeel->forum_id === $deel->forum_id} selected="selected"{/if}>{$newDeel->titel}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		{/if}
	</div>
{/capture}

{$smarty.capture.navlinks}

<h1>{$deel->titel}{if !isset($deel->forum_id)}{include file='MVC/forum/rss_link.tpl'}{/if}</h1>

<table id="forumtabel">
	<thead>
		<tr>
			<th>Titel</th>
			<th>Reacties</th>
			<th class="text-center">Auteur</th>
			<th>Recente wijziging</th>
		</tr>
	</thead>
	<tbody>

		{if !$deel->hasForumDraden()}
			<tr>
				<td colspan="4">Dit forum is nog leeg.</td>
			</tr>
		{/if}

		{foreach from=$deel->getForumDraden() item=draad}
			{include file='MVC/forum/draad_lijst.tpl'}
		{/foreach}

		{if $paging}
			<tr>
				<th colspan="4">
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
			<td colspan="4">
				<div class="forumdeel-omschrijving">
					<div class="float-right">{$smarty.capture.navlinks}</div>
					<h1>{$deel->titel}</h1>
					{$deel->omschrijving}
				</div>
			</td>
		</tr>

		{if $deel->magPosten()}
			<tr>
				<td colspan="4">
					<br />
					<table>
						{include file='MVC/forum/post_form.tpl' draad=null}
					</table>
				</td>
			</tr>
		{/if}

	</tbody>
</table>