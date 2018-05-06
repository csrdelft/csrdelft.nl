{getMelding()}

<h1>{$titel}</h1>

{if $resultaten}
	<table id="forumtabel">
		{foreach from=$resultaten item=draad}
			<thead>
				<tr>
					<th class="niet-dik">
						{if CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief'}
							{$draad->datum_tijd|reldate}
						{else}
							{$draad->datum_tijd}
						{/if}
					</th>
					<th>
						{if $draad->wacht_goedkeuring}
							<span title="Nieuw onderwerp in {$draad->getForumDeel()->titel}">
								<small class="niet-dik">[<a href="/forum/deel/{$draad->forum_id}">{$draad->getForumDeel()->titel}</a>]</small>
								{$draad->titel}
								{icon get="new"}
							</span>
						{else}
							<small class="niet-dik">[<a href="/forum/deel/{$draad->forum_id}">{$draad->getForumDeel()->titel}</a>]</small>
							<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}"{if $draad->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>
								{$draad->titel}
							</a>
							{if $draad->belangrijk}
								{icon get=$draad->belangrijk title="Dit onderwerp is door het bestuur aangemerkt als belangrijk"}
							{elseif $draad->gesloten}
								{icon get="lock" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
							{/if}
						{/if}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$draad->getForumPosts() item=post}
					{include file='forum/post_lijst.tpl' deel=$draad->getForumDeel()}
					<tr class="tussenschot">
						<td colspan="2"></td>
					</tr>
				{/foreach}
			</tbody>
		{/foreach}
		{if isset($query)}
			<thead>
				<tr>
					<th colspan="2">
						{sliding_pager baseurl="/forum/zoeken/"|cat:$query|cat:"/"
					pagecount=ForumDradenModel::instance()->getHuidigePagina() curpage=ForumDradenModel::instance()->getHuidigePagina()
					separator=" &nbsp;"}
						&nbsp;<a href="/forum/zoeken/{$query}/{ForumDradenModel::instance()->getAantalPaginas(0)}">verder zoeken</a>
					</th>
				</tr>
			</thead>
		{/if}
	</table>

	<h1>{$titel}</h1>
	{$breadcrumbs}

{else}
	Geen resultaten.
{/if}