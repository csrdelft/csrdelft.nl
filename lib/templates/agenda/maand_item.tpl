<li title="{$item->getBeschrijving()}" id="item-{$item->item_id}" parentid="items-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
	{if $item->magBeheren()}
		<div class="beheren">
			<a href="/agenda/verwijderen/{$item->item_id}" class="post confirm" title="Dit agenda-item definitief verwijderen">
				{icon get="verwijderen"}
			</a>
			<a href="/agenda/bewerken/{$item->item_id}" class="post popup" title="Dit agenda-item bewerken">
				{icon get="bewerken"}
			</a>
		</div>
	{/if}
	{if !$item->isHeledag()}
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"}
			{if !preg_match('/(00:00|23:59):[0-9]{2}$/', $item->eind_moment)}
				-
				{$item->getEindMoment()|date_format:"%R"}
			{/if}
		</div>
	{/if}
	<div class="hoverIntent">
		{if $item->getLink()}
			<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">{$item->getTitel()}</a>
		{else}
			<span title="{$item->getBeschrijving()}">{$item->getTitel()}</span>
		{/if}
		{if $item->getLocatie()}
			<a href="https://maps.google.nl/maps?q={$item->getLocatie()|htmlspecialchars}">{icon get=map title=Kaart}</a>
			<div class="hoverIntentContent">
				{"[kaart]"|cat:$item->getLocatie()|cat:"[/kaart]"|bbcode}
			</div>
		{/if}
	</div>
</li>