{$view->getMelding()}

<form id="forum_zoeken" action="/forum/zoeken" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum" class="forumGrootlink">Forum</a>
	</div>
{/capture}

{$smarty.capture.navlinks}

<h1>{$view->getTitel()}</h1>

{if $resultaten}
	<table id="forumtabel">
		{foreach from=$resultaten item=draad}
			<thead>
				<tr>
					<th style="font-weight: normal;">
						{if LoginLid::instelling('forum_datumWeergave') === 'relatief'}
							{$draad->datum_tijd|reldate}
						{else}
							{$draad->datum_tijd}
						{/if}
					</th>
					<th>{$draad->titel}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$draad->getForumPosts() item=post}
					{include file='MVC/forum/post_lijst.tpl'}
					<tr class="tussenschot">
						<td colspan="2"></td>
					</tr>
				{/foreach}
			</tbody>
		{/foreach}
	</table>

	<h1>{$view->getTitel()}</h1>
	{$smarty.capture.navlinks}

{else}
	Geen resultaten.
{/if}