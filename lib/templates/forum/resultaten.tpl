{getMelding()}

<h1>{$titel}</h1>

{if $resultaten}
	<div class="forum-zoeken">
		<table id="forumtabel">
			{foreach from=$resultaten item=draad}
				<div class="forum-zoeken-header">

					<div>
						{if $draad->wacht_goedkeuring}
							<span title="Nieuw onderwerp in {$draad->getForumDeel()->titel}">
								{$draad->titel}
								<span>
									[<a href="/forum/deel/{$draad->forum_id}">{$draad->getForumDeel()->titel}</a>]
								</span>
								{icon get="new"}
							</span>
						{else}
							<a id="{$draad->draad_id}"
								 href="/forum/onderwerp/{$draad->draad_id}"{if $draad->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>
								{$draad->titel}
							</a>
							{if $draad->belangrijk}
								{icon get=$draad->belangrijk title="Dit onderwerp is door het bestuur aangemerkt als belangrijk"}
							{elseif $draad->gesloten}
								{icon get="lock" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
							{/if}
							<span>
								[<a href="/forum/deel/{$draad->forum_id}">{$draad->getForumDeel()->titel}</a>]
							</span>
						{/if}
					</div>
					<div class="niet-dik">
						{if CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief'}
							{$draad->datum_tijd|reldate}
						{else}
							{$draad->datum_tijd}
						{/if}
					</div>
				</div>
				<div class="forum-zoeken-bericht">
					{foreach from=$draad->getForumPosts() item=post}
						{include file='forum/post_lijst.tpl' deel=$draad->getForumDeel()}
						<div class="tussenschot"></div>
					{/foreach}
				</div>
			{/foreach}
			{if isset($query)}
				<div class="forum-zoeken-footer">
					{sliding_pager baseurl="/forum/zoeken/"|cat:$query|cat:"/"
					pagecount=ForumDradenModel::instance()->getHuidigePagina() curpage=ForumDradenModel::instance()->getHuidigePagina()
					separator=" &nbsp;"}
					&nbsp;<a href="/forum/zoeken/{$query}/{ForumDradenModel::instance()->getAantalPaginas(0)}">verder zoeken</a>
				</div>
			{/if}
		</table>
	</div>
	<h1>{$titel}</h1>
	{$breadcrumbs}

{else}
	Geen resultaten.
{/if}
