<div id="zijbalk_agenda">
	<div class="zijbalk-kopje">
		<a href="/agenda/" title="Agenda">Agenda</a>
	</div>
	{foreach from=$items item=item}
		<div class="item">
			{if $item->getUrl()}
			<a href="{$item->getUrl()}" title="{$item->getBeschrijving()}">
				{else}
				<a title="{$item->getBeschrijving()}"
					 href="/agenda/maand/{$item->getBeginMoment()|date_format:"%Y/%m/%d"}#dag-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
					{/if}
					<span class="zijbalk-moment">{$item->getBeginMoment()|zijbalk_date_format}</span>&nbsp;{$item->getTitel()}
				</a>
		</div>
	{/foreach}
</div>
