{assign var=verborgen value=AgendaVerbergenModel::instance()->isVerborgen($item)}
<li id="item-{str_replace('@', '-', str_replace('.', '-', $item->getUUID()))}" {if $verborgen}class="offtopic"{/if} title="{$item->getBeschrijving()}" parentid="items-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
	<a href="/agenda/verbergen/{$item->getUUID()}" class="beheren post" title="{if $verborgen}Toon{else}Verberg{/if} dit agenda item">{if $verborgen}{icon get=shading}{else}{icon get=eye}{/if}</a>
	{if $item instanceof Groep AND $item->mag(A::Wijzigen)}
		<a href="{$item->getUrl()}wijzigen" class="beheren" title="Wijzig {htmlspecialchars($item->naam)}">{icon get="bewerken"}</a>
	{elseif $item instanceof AgendaItem AND $item->magBeheren()}
		<a href="/agenda/bewerken/{$item->item_id}" class="beheren post popup" title="Dit agenda-item bewerken">{icon get="bewerken"}</a>
		<a href="/agenda/verwijderen/{$item->item_id}" class="beheren post confirm" title="Dit agenda-item verwijderen">{icon get="verwijderen"}</a>
	{/if}
	{if $item instanceof Profiel}
		{icon get="verjaardag"}
		{$item->getLink()}
	{elseif $item instanceof Bijbelrooster}
		{icon get="book_open"}
		{$item->getLink(true)}
	{elseif $item instanceof Maaltijd}
		<img src="/plaetjes/maalcie/cutlery.png" width="16" height="16" alt="cutlery" class="icon" />
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"} - {$item->getEindMoment()|date_format:"%R"}
		</div>
		<a href="{$item->getLink()}">
			{$item->getTitel()}
		</a>
	{elseif $item instanceof CorveeTaak}
		{if $item->getCorveeFunctie()->naam|stristr:"klus"}
			<img src="/plaetjes/maalcie/drill.png" width="16" height="16" alt="drill" class="icon" />
		{else}
			{icon get="paintcan"}
		{/if}
		<a href="{$item->getLink()}">
			{$item->getTitel()}
		</a>
	{elseif $item instanceof Agendeerbaar}
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
				<a href="{$item->getLink()}">{$item->getTitel()}</a>
			{else}
				{$item->getTitel()}
			{/if}
			{if $item->getLocatie()}
				<a href="https://maps.google.nl/maps?q={$item->getLocatie()|htmlspecialchars}">{icon get=map title=Kaart}</a>
				<div class="hoverIntentContent">
					{"[kaart]"|cat:$item->getLocatie()|cat:"[/kaart]"|bbcode}
				</div>
			{/if}
		</div>
	{/if}
</li>