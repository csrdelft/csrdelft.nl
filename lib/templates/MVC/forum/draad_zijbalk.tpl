{strip}
	<div class="item">
		{if date('d-m', $timestamp) === date('d-m')}
			{$timestamp|date_format:"%H:%M"}
		{elseif strftime('%U', $timestamp) === strftime('%U')}
			<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
		{else}
			{$timestamp|date_format:"%d-%m"}
		{/if}
		&nbsp;
		<a href="/forumpost/{$post_id}" title="[{$titel}] {$naam}: {$post_fragment}"{if $opvallend} class="opvallend"{/if}>{$linktekst}</a><br />
	</div>
{/strip}