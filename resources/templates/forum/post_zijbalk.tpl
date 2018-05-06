{strip}
{assign var=timestamp value=strtotime($post->datum_tijd)}
{assign var=draad value=$post->getForumDraad()}
<div class="item">
	<a href="/forum/reactie/{$post->post_id}#{$post->post_id}" title="{$draad->titel}"{if $draad->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>
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