{assign var=verborgen value=CsrDelft\model\agenda\AgendaVerbergenModel::instance()->isVerborgen($item)}
<li id="item-{str_replace('@', '-', str_replace('.', '-', $item->getUUID()))}" {if $verborgen}class="offtopic"{/if} title="{$item->getBeschrijving()}" parentid="items-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
	<a href="/agenda/verbergen/{$item->getUUID()}" class="beheren post" title="{if $verborgen}Toon{else}Verberg{/if} dit agenda item">{if $verborgen}{icon get=shading}{else}{icon get=eye}{/if}</a>
	{if $item instanceof CsrDelft\model\entity\groepen\AbstractGroep AND $item->mag(CsrDelft\model\entity\security\AccessAction::Wijzigen)}
		<a href="{$item->getUrl()}wijzigen" class="beheren" title="Wijzig {htmlspecialchars($item->naam)}">{icon get="bewerken"}</a>
	{elseif $item instanceof CsrDelft\model\entity\agenda\AgendaItem AND $item->magBeheren()}
		<a href="/agenda/bewerken/{$item->item_id}" class="beheren post popup" title="Dit agenda-item bewerken">{icon get="bewerken"}</a>
		<a href="/agenda/verwijderen/{$item->item_id}" class="beheren post confirm" title="Dit agenda-item definitief verwijderen">{icon get="verwijderen"}</a>
	{/if}
	{if $item instanceof CsrDelft\model\entity\Profiel}
		{icon get="verjaardag"}
		{$item->getUrl()}
	{elseif $item instanceof CsrDelft\model\entity\maalcie\Maaltijd}
		<img src="/images/maalcie/cutlery.png" width="16" height="16" alt="cutlery" class="icon" />
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"} - {$item->getEindMoment()|date_format:"%R"}
		</div>
		<a href="{$item->getUrl()}">
			{$item->getTitel()}
		</a>
	{elseif $item instanceof CsrDelft\model\entity\CorveeTaak}
		{if $item->getCorveeFunctie()->naam|stristr:"klus"}
			<img src="/images/maalcie/drill.png" width="16" height="16" alt="drill" class="icon" />
		{else}
			{icon get="paintcan"}
		{/if}
		<a href="{$item->getUrl()}">
			{$item->getTitel()}
		</a>
	{elseif $item instanceof CsrDelft\model\entity\agenda\Agendeerbaar}
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
			{if $item->getUrl()}
				<a href="{$item->getUrl()}">{$item->getTitel()}</a>
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
