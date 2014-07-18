<tr class="forumdeel kleur{cycle values="0,1"}">
	<td class="titel hoverIntent">
		<a href="/forum/beheren/{$deel->forum_id}" class="post popup hoverIntentContent" style="float: right;">{icon get="bewerken"}</a>
		<a href="/forum/deel/{$deel->forum_id}">{$deel->titel}</a>
		<br />{$deel->omschrijving}
	</td>
	<td class="reacties">{$deel->aantal_draden}</td>
	<td class="reacties">{$deel->aantal_posts}</td>
	<td class="reactiemoment">
		{if $deel->laatst_gewijzigd}
			{if LoginLid::instelling('forum_datumWeergave') === 'relatief'}
				{$deel->laatst_gewijzigd|reldate}
			{else}
				{$deel->laatst_gewijzigd}
			{/if}
			<br /><a href="/forum/reactie/{$deel->laatste_post_id}#{$deel->laatste_post_id}">bericht</a> 
			door {$deel->laatste_lid_id|csrnaam:'user'}
		{/if}
	</td>
</tr>