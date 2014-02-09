{strip}
<div id="zijbalk_agenda">
	<h1>
		<a href="/agenda/" title="Agenda">Agenda</a>
	</h1>
	{foreach from=$items item=item}
		{if $item instanceof Lid}
			{* geen verjaardagen hier *}
		{else}
			<div class="item">
				{if date('d-m', $item->getBeginMoment()) === date('d-m')}
					{$item->getBeginMoment()|date_format:"%H:%M"}
				{elseif strftime('%U', $item->getBeginMoment()) === strftime('%U')}
					<div class="zijbalk-dag">{$item->getBeginMoment()|date_format:"%a"}&nbsp;</div>{$item->getBeginMoment()|date_format:"%d"}
				{else}
					{$item->getBeginMoment()|date_format:"%d-%m"}
				{/if}
				&nbsp;
				<a href="/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/#dag-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}" title="{$item->getTitel()}">
					{$item->getTitel()|truncate:25:"…":true}
				</a>
			</div>
		{/if}
	{/foreach}
</div>
{/strip}