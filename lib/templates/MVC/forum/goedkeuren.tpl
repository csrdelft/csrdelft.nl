{$view->getMelding()}

<form id="forum_zoeken" action="/forum/zoeken" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum" class="forumGrootlink">Forum</a>
	</div>
{/capture}

{$smarty.capture.navlinks}
<h1>{$view->getTitel()}</h1>

{if $delen}
	<table id="forumtabel">
		{foreach from=$delen item=deel}
			{foreach from=$deel->getForumDraden() item=draad}
				<thead>
					<tr>
						<th>Draad:</th>
						<th>{$draad->titel}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$draad->getForumPosts() item=post}
						{include file='MVC/forum/post_lijst.tpl'}
					{/foreach}
				</tbody>
			{/foreach}
		{/foreach}
	</table>

	<h1>{$view->getTitel()}</h1>
	{$smarty.capture.navlinks}

{else}
	Geen wachtrij.
{/if}