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
					{if $item->getLink()}
						<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
					{else}
						<a title="{$item->getBeschrijving()}" href="/agenda/maand/{$item->getBeginMoment()|date_format:"%Y/%m/%d"}#dag-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
					{/if}
					{if date('d-m', $item->getBeginMoment()) === date('d-m')}
						{$item->getBeginMoment()|date_format:"%H:%M"}
					{elseif strftime('%U', $item->getBeginMoment()) === strftime('%U')}
						<div class="zijbalk-dag">{$item->getBeginMoment()|date_format:"%a"}&nbsp;</div>{$item->getBeginMoment()|date_format:"%d"}
					{else}
						{$item->getBeginMoment()|date_format:"%d-%m"}
					{/if}
					&nbsp;
					{$item->getTitel()}
						</a>
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}