<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

<div class="forumNavigatie">
	<h1>Forum</h1>
</div>
{$view->getMelding()}

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th>{$cat->titel} <span class="forumcategorie-omschrijving">{$cat->omschrijving}</span></th>
				<th>Onderwerpen</th>
				<th>Berichten</th>
				<th>Recent</th>
			</tr>
		</thead>
		<tbody>
			{if !$cat->hasForumDelen()}
				<tr>
					<td colspan="4">Deze categorie is leeg.</td>
				</tr>
			{/if}
			{foreach from=$cat->getForumDelen() item=deel}
				<tr class="forumdeel kleur{cycle values="0,1"}">
					<td class="titel">
						<a href="/forumdeel/{$deel->forum_id}">{$deel->titel}</a>
						<br /><span class="forumdeel-omschrijving">{$deel->omschrijving}</span>
					</td>
					<td class="reacties">{$deel->aantal_draden}</td>
					<td class="reacties">{$deel->aantal_posts}</td>
					<td class="reactiemoment">
						{if $deel->laatst_gewijzigd}
							{if $loginlid->getInstelling('forum_datumWeergave') === 'relatief'}
								{$deel->laatst_gewijzigd|reldate}
							{else}
								{$deel->laatst_gewijzigd}
							{/if}
							<br /><a href="/forumdraad/{$deel->laatste_draad_id}#{$deel->laatste_post_id}">bericht</a> 
							door {$deel->laatste_lid_id|csrnaam:'user'}
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}
</table>