<tr class="forumdraad kleur{cycle values="0,1"}">
	<td class="titel">
		{if $draad->wacht_goedkeuring}
			<small class="niet-dik">[ter goedkeuring...]</small>
		{/if}
		<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}"{if $draad->onGelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>
			{if $draad->gesloten}
				{icon get="slotje" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
			{elseif $draad->belangrijk}
				{icon get="belangrijk" title="Dit onderwerp is door het bestuur aangemerkt als belangrijk."}
			{elseif $draad->plakkerig}
				{icon get="plakkerig" title="Dit onderwerp is plakkerig, het blijft bovenaan."}
			{/if}
			{$draad->titel}
		</a>
		{sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/"
			pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=0
			txt_pre="&nbsp;[ " txt_post=" ]" link_current=true}
	</td>
	<td class="reacties">{$draad->aantal_posts}</td>
	<td>{$draad->uid|csrnaam:'user'}</td>
	<td class="reactiemoment">
		{if LidInstellingen::get('forum', 'datumWeergave') === 'relatief'}
			{$draad->laatst_gewijzigd|reldate}
		{else}
			{$draad->laatst_gewijzigd}
		{/if}
		<br /><a href="/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}">bericht</a>
		door {$draad->laatste_wijziging_uid|csrnaam:'user'}
	</td>
</tr>