{SimpleHtml::getMelding()}

{include file='MVC/forum/zoek_form.tpl'}

<div class="forumNavigatie">
	<a href="/forum/recent" class="forumGrootlink">Recent</a>
</div>
<h1>Forum</h1>

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th class="hoverIntent">
					<div class="hoverIntentContent" style="float: right;">
						<a href="/forum/toevoegen/{$cat->categorie_id}" class="post popup">{icon get="add"}</a>
					</div>
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