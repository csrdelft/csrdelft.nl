<div id="item-{$item->item_id}">
	{if $magBeheren}
		<a class="beheren" href="/agenda/verwijderen/{$item->item_id}/" onclick="return confirm('Weet u zeker dat u dit agenda-item wilt verwijderen?');" title="verwijderen">
			{icon get="verwijderen"}
		</a>
		<a class="beheren" href="/agenda/bewerken/{$item->item_id}/" title="bewerken">
			{icon get="bewerken"}
		</a>
	{/if}
	{if !$item->isHeledag()}
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"}
		</div>
	{/if}
	<span title="{$item->getBeschrijving()}">{$item->getTitel()}</span>
</div>