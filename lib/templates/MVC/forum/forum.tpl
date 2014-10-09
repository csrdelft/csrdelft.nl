{getMelding()}

{$zoekform->view()}

{if LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/aanmaken" class="knop round post modal confirm" title="Deelforum aanmaken">{icon get="add"} </a>
	</div>
{/if}

{include file='MVC/forum/head_buttons.tpl'}

<div class="breadcrumbs">
	<a href="/forum/recent" class="forumGrootlink">Recent</a>
</div>

<h1>Forum<a href="{LoginModel::instance()->getLid()->getRssLink()}" class="float-right" title="Persoonlijke RSS-feed forum">{icon get="feed"}</a></h1>

<div class="float-right">
	<a href="{LoginModel::instance()->getLid()->getRssLink()}" title="Persoonlijke RSS-feed forum">{icon get="feed"}</a>
</div>

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th>
					{$cat->titel}
					<span class="forumcategorie-omschrijving">{$cat->omschrijving}</span>
				</th>
				<th>Onderwerpen</th>
				<th>Berichten</th>
				<th>Recente wijziging</th>
			</tr>
		</thead>
		<tbody>
			{if !$cat->hasForumDelen()}
				<tr>
					<td colspan="4">Deze categorie is leeg.</td>
				</tr>
			{/if}
			{foreach from=$cat->getForumDelen() item=deel}
				{include file='MVC/forum/deel_lijst.tpl'}
			{/foreach}
		</tbody>
	{/foreach}
</table>