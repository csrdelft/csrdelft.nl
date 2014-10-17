<li id="item-{$item->item_id}" parentid="items-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
	{if $item->magBeheren()}
		<div class="beheren">
			<a href="/agenda/verwijderen/{$item->item_id}" class="post confirm" title="Dit agenda-item definitief verwijderen">
				{icon get="verwijderen"}
			</a>
				<a href="/agenda/bewerken/{$item->item_id}" class="post modal" title="Dit agenda-item bewerken">
				{icon get="bewerken"}
			</a>
		</div>
	{/if}
	{if !$item->isHeledag()}
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"}
		</div>
	{/if}
	<div class="hoverIntent">
		{if $item->getLink()}
			<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">{$item->getTitel()}</a>
		{else}
			<span title="{$item->getBeschrijving()}">{$item->getTitel()}</span>
		{/if}
		{if $item->getLocatie()}
			<a href="http://maps.google.nl/maps?q={$item->getLocatie()|htmlspecialchars}">{icon get=map title=Kaart}</a>
			<div class="hoverIntentContent">
				{"[kaart]"|cat:$item->getLocatie()|cat:"[/kaart]"|bbcode}
			</div>
		{/if}
	</div>
</li>