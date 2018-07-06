{strip}
	{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
	<div class="item" id="forumdraad-row-{$draad->draad_id}">
		<a href="/forum/onderwerp/{$draad->draad_id}{if CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}" title="{$draad->titel}"{toegang P_LOGGED_IN}{if $draad->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}{/toegang}>
			<span class="zijbalk-moment">{$timestamp|zijbalk_date_format}</span>&nbsp;{$draad->titel}
		</a>
	</div>
{/strip}
