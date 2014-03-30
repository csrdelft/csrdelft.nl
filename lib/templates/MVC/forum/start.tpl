<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

<div class="forumNavigatie">
	<h1 style="width: 200px;">Forum</h1>
</div>
{$view->getMelding()}

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th>{$cat->titel} <span class="forumcategorie-omschrijving">{$cat->omschrijving}</span></th>
				<th>Onderwerpen</th>
				<th>Berichten</th>
				<th>Verandering</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$cat->getForumDelen() item=deel}
				<tr class="forumdeel">
					<td class="titel">
						<a href="/forumdeel/{$deel->forum_id}">{$deel->titel}</a>
						<br /><span class="forumdeel-omschrijving">{$deel->omschrijving}</span>
					</td>
					<td class="reacties">{$deel->aantal_draden}</td>
					<td class="reacties">{$deel->aantal_posts}</td>
					<td class="reactiemoment">
						{if $deel->laatst_gepost}
							{if $loginlid->getInstelling('forum_datumWeergave') === 'relatief'}
								{$deel->laatst_gepost|reldate}
							{else}
								{$deel->laatst_gepost}
							{/if}
							<br /><a href="/forumpost/{$deel->laatste_post_id}">bericht</a> 
							door {$deel->laatste_lid_id|csrnaam:'user'}
						{else}
							nog geen berichten
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}
</table>