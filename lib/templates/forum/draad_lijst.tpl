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
		{if $draad->getAantalOngelezenPosts() > 0}
			<span class="badge">{$draad->getAantalOngelezenPosts()}</span>
		{/if}
		{if !isset($deel->forum_id)} 
			<span class="lichtgrijs">[{$draad->getForumDeel()->titel}]</span> 
		{/if}
	</td>
	<td class="laatstewijziging">
		{if LidInstellingen::get('forum', 'datumWeergave') === 'relatief'}
			{$draad->laatst_gewijzigd|reldate}
		{else}
			{$draad->laatst_gewijzigd}
		{/if}
	</td>
	<td class="laatstewijziging">
		<a href="/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}">bericht</a>
		door {ProfielModel::getLink($draad->laatste_wijziging_uid, 'user')}
	</td>
</tr>