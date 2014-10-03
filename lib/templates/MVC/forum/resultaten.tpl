{SimpleHtml::getMelding()}

{capture name='navlinks'}
	<div class="breadcrumbs">
		<a href="/forum" class="forumGrootlink">Forum</a>
	</div>
{/capture}

{$smarty.capture.navlinks}

<h1>{$titel}</h1>

{if $resultaten}
	<table id="forumtabel">
		{foreach from=$resultaten item=draad}
			<thead>
				<tr>
					<th class="niet-dik">
						{if LidInstellingen::get('forum', 'datumWeergave') === 'relatief'}
							{$draad->datum_tijd|reldate}
						{else}
							{$draad->datum_tijd}
						{/if}
					</th>
					<th>
						{if $draad->wacht_goedkeuring}
							<span title="Nieuw onderwerp in {$delen[$draad->forum_id]->titel}">
								<small class="niet-dik">[{$delen[$draad->forum_id]->titel}]</small>
								{$draad->titel}
								{icon get="new"}
							</span>
						{else}
							<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}"{if !$draad->alGelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>
								<small class="niet-dik">[{$delen[$draad->forum_id]->titel}]</small>
								{$draad->titel}
								{if $draad->gesloten}
									{icon get="slotje" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
								{elseif $draad->belangrijk}
									{icon get="belangrijk" title="Dit onderwerp is door het bestuur aangemerkt als belangrijk."}
								{/if}
							</a>
						{/if}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$draad->getForumPosts() item=post}
					{include file='MVC/forum/post_lijst.tpl' deel=$delen[$draad->forum_id]}
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
	{$smarty.capture.navlinks}

{else}
	Geen resultaten.
{/if}