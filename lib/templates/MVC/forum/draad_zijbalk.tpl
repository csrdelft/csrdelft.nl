{strip}
	{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
	<div class="item hoverIntent" id="forumdraad-row-{$draad->draad_id}">
		{*include file='MVC/forum/post_preview.tpl'*}
		{if date('d-m', $timestamp) === date('d-m')}
			{$timestamp|date_format:"%H:%M"}
		{elseif strftime('%U', $timestamp) === strftime('%U')}
			<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
		{else}
			{$timestamp|date_format:"%d-%m"}
		{/if}
		&nbsp;
		<a href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}" title="{$draad->titel}"{if !$draad->alGelezen()} style="{LidInstellingen::instance()->getTechnicalValue('forum', 'ongelezenWeergave')}"{/if}>
			{$draad->titel|truncate:25:"…":true}
		</a>
		{if !$draad->belangrijk}
			<div class="hoverIntentContent" style="float: right;">
				<a href="/forum/optout/{$draad->draad_id}">{icon get="bullet_delete"}</a>
			</div>
		{/if}
	</div>
{/strip}