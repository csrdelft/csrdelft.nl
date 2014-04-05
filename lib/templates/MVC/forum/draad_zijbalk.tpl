{strip}
	<div class="item">
		{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
		{if date('d-m', $timestamp) === date('d-m')}
			{$timestamp|date_format:"%H:%M"}
		{elseif strftime('%U', $timestamp) === strftime('%U')}
			<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
		{else}
			{$timestamp|date_format:"%d-%m"}
		{/if}
		&nbsp;
		<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'openDraadPagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'openDraadPagina') == 'laatste'}#reageren{/if}"{if !$draad->alGelezen()} class="opvallend"{/if}>
			{$draad->titel|truncate:25:"…":true}
		</a>
		<br />
	</div>
{/strip}