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
			<br /><a href="/forumpost/{$deel->laatste_post_id}#{$deel->laatste_post_id}">bericht</a> 
			door {$deel->laatste_lid_id|csrnaam:'user'}
		{/if}
	</td>
</tr>