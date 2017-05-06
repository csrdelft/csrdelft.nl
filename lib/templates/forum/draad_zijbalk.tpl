{strip}
	{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
	<div class="item" id="forumdraad-row-{$draad->draad_id}">
		<a href="/forum/onderwerp/{$draad->draad_id}{if CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}" title="{$draad->titel}"{if CsrDelft\model\security\LoginModel::mag('P_LOGGED_IN') AND $draad->isOngelezen()} class="{CsrDelft\model\security\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>
			{if date('d-m', $timestamp) === date('d-m')}
				{$timestamp|date_format:"%H:%M"}
			{elseif strftime('%U', $timestamp) === strftime('%U')}
				<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
			{else}
				{$timestamp|date_format:"%d-%m"}
			{/if}
			&nbsp;
			{$draad->titel}
		</a>
	</div>
{/strip}