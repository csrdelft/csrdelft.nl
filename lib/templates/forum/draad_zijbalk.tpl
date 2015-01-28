{strip}
	{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
	<div class="item" id="forumdraad-row-{$draad->draad_id}">
		<a href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}" title="{$draad->titel}"{if $draad->isOngelezen()} class="{LidInstellingen::get('forum', 'ongelezenWeergave')}"{/if}>
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