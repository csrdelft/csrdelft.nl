<tr class="forumdraad kleur{cycle values="0,1"}">
	<td class="titel">
		{if $draad->wacht_goedkeuring}
			[ter goedkeuring...]
		{/if}
		<a href="/forumdraad/{$draad->draad_id}"{if !$draad->alGelezen()} class="updatedTopic"{/if}>
			{if $draad->plakkerig}
				<img src="{icon get="plakkerig" notag=true}" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;
			{/if}
			{if $draad->gesloten}
				<img src="{icon get="slotje" notag=true}" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;
			{/if}
			{$draad->titel}
		</a>
		{sliding_pager baseurl="/forumdraad/"|cat:$draad->draad_id|cat:"/"
			pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=0
			txt_pre="&nbsp;[ " txt_post=" ]" link_current=true}
	</td>
	<td class="reacties">{$draad->aantal_posts}</td>
	<td class="reacties">{$draad->lid_id|csrnaam:'user'}</td>
	<td class="reactiemoment">
		{if $loginlid->getInstelling('forum_datumWeergave') === 'relatief'}
			{$draad->laatst_gewijzigd|reldate}
		{else}
			{$draad->laatst_gewijzigd}
		{/if}
		<br /><a href="/forumdraad/{$draad->draad_id}#post{$draad->laatste_post_id}">bericht</a> 
		door {$draad->laatste_lid_id|csrnaam:'user'}
	</td>
</tr>