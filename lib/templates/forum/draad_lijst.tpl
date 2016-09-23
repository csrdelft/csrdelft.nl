<tr class="forumdraad kleur{cycle values="0,1"}">
	<td class="titel">
		{if $draad->wacht_goedkeuring}
			<small class="niet-dik">[ter goedkeuring...]</small>
		{/if}
		{if $draad->belangrijk}
			{icon get=$draad->belangrijk title="Dit onderwerp is door het bestuur aangemerkt als belangrijk"}
		{elseif $draad->plakkerig}
			{icon get="note" title="Dit onderwerp is plakkerig, het blijft bovenaan"}
		{elseif $draad->gesloten}
			{icon get="lock" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
		{else}
			<div class="inline" style="width: 16px;"></div>
		{/if}
		<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}"{if $draad->isOngelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>{$draad->titel}</a>
		{if LoginModel::mag('P_LOGGED_IN') AND $draad->getAantalOngelezenPosts() > 0}
			<span class="badge">{$draad->getAantalOngelezenPosts()}</span>
		{/if}
		{if !isset($deel->forum_id)} 
			<span class="float-right lichtgrijs">[<a href="/forum/deel/{$draad->getForumDeel()->forum_id}" class="lichtgrijs">{$draad->getForumDeel()->titel}</a>]</span> 
		{/if}
	</td>
	<td class="datumwijziging">
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