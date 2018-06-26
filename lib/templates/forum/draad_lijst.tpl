<div class="forum-deel-draad kleur{cycle values="0,1"}">
	<div class="titel">
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
		<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}"{toegang P_LOGGED_IN}{if $draad->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}{/toegang}>{$draad->titel}</a>
		{toegang P_LOGGED_IN}
		{if $draad->getAantalOngelezenPosts() > 0}
			<span class="badge">{$draad->getAantalOngelezenPosts()}</span>
		{/if}
		{/toegang}
		{if !isset($deel->forum_id)}
			<span class="lichtgrijs">[<a href="/forum/deel/{$draad->getForumDeel()->forum_id}" class="lichtgrijs">{$draad->getForumDeel()->titel}</a>]</span>
		{/if}
	</div>
	<div class="datumwijziging">
		{if CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief'}
			{$draad->laatst_gewijzigd|reldate}
		{else}
			{$draad->laatst_gewijzigd}
		{/if}
	</div>
	<div class="laatstewijziging">
		<a href="/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}">bericht</a>
		door {CsrDelft\model\ProfielModel::getLink($draad->laatste_wijziging_uid, 'user')}
	</div>
</div>
