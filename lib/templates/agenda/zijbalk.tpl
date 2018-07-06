{strip}
	<div id="zijbalk_agenda">
		<div class="zijbalk-kopje">
			<a href="/agenda/" title="Agenda">Agenda</a>
		</div>
		{foreach from=$items item=item}
			{if $item instanceof CsrDelft\model\entity\Profiel}
				{* geen verjaardagen hier *}
			{else}
				<div class="item">
					{if $item->getLink()}
					<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
					{else}
					<a title="{$item->getBeschrijving()}"
						 href="/agenda/maand/{$item->getBeginMoment()|date_format:"%Y/%m/%d"}#dag-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
					{/if}
							<span class="zijbalk-moment">{$item->getBeginMoment()|zijbalk_date_format}</span>&nbsp;{$item->getTitel()}
						</a>
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}
