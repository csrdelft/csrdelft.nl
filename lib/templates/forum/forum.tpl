{getMelding()}

{$zoekform->view()}

{if LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/aanmaken" class="btn round post popup confirm" title="Deelforum aanmaken">{icon get="add"} </a>
	</div>
{/if}

{include file='forum/head_buttons.tpl'}

<h1>Forum{include file='forum/rss_link.tpl'}</h1>

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th>
					<a name="{$cat->categorie_id}">{$cat->titel}</a>
					<span class="forumcategorie-omschrijving">{$cat->omschrijving}</span>
				</th>
				<th class="reacties">Onderwerpen</th>
				<th class="reacties">Berichten</th>
				<th class="reactiemoment">Recente wijziging</th>
			</tr>
		</thead>
		<tbody>
			{if !$cat->hasForumDelen()}
				<tr>
					<td colspan="4">Deze categorie is leeg.</td>
				</tr>
			{/if}
			{foreach from=$cat->getForumDelen() item=deel}
				{include file='forum/deel_lijst.tpl'}
			{/foreach}
		</tbody>
	{/foreach}
</table>

<h2>Berichten per dag</h2>
{if LoginModel::mag('P_LOGGED_IN')}
	{include file='forum/stats_grafiek.tpl'}
{/if}