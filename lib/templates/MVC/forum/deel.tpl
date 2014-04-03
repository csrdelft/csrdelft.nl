{$view->getMelding()}

<form id="forum_zoeken" action="/forum/zoeken" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum" class="forumGrootlink">Forum</a>
	</div>
{/capture}

{$smarty.capture.navlinks}
<h1>{$deel->titel}</h1>

<table id="forumtabel">
	<thead>
		<tr>
			<th>Titel</th>
			<th>Reacties</th>
			<th>Auteur</th>
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
	</tbody>
	<thead>
		<tr>
			<th colspan="4">
				{sliding_pager baseurl="/forum/deel/"|cat:$deel->forum_id|cat:"/"
				pagecount=ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) curpage=ForumDradenModel::instance()->getHuidigePagina()
				separator=" &nbsp;"}
			</th>
		</tr>
	</thead>
	<tbody>
		{if $deel->magPosten()}
			{include file='MVC/forum/draad_form.tpl'}
		{/if}
	</tbody>
</table>

<h1>{$deel->titel}</h1>
{$smarty.capture.navlinks}