<a href="/actueel/agenda/icalendar/" class="knop" style="float: right" title="Link naar Icalender export">{icon get="calendar_link"}</a><h1>Agenda {$datum|date_format:"%B %Y"}</h1>

{$melding}

<div class="maandnavigatie">
	<a class="knop" href="{$urlVorige}" style="float: left;" >&laquo; Vorige maand</a>
	<a class="knop" href="{$urlVolgende}" style="float: right;">Volgende maand &raquo;</a>
</div>
<table class="agenda" id="maand">

	<tr>
		<th> </th>
		<th>Zondag</th>
		<th>Maandag</th>
		<th>Dinsdag</th>
		<th>Woensdag</th>
		<th>Donderdag</th>
		<th>Vrijdag</th>
		<th>Zaterdag</th>
	</tr>
	{foreach from=$weken key=weeknr item=dagen}
		<tr id="{if strftime('%U', $dag.datum) == strftime('%U')-1}dezeweek{/if}">
			<th>{$weeknr}</th>
			{foreach from=$dagen key=dagnr item=dag}
				<td class="dag {if strftime('%m', $dag.datum) != strftime('%m', $datum)}anderemaand{/if}{if date('d-m', $dag.datum)==date('d-m')} vandaag{/if}"
				id="dag-{$dag.datum|date_format:"%Y-%m-%d"}">
					<div class="meta">
						{if	$magToevoegen}
							<a class="toevoegen" href="/actueel/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}/"
								title="Item toevoegen">
								{icon get="toevoegen"}
							</a>
						{/if}
						{$dagnr}
					</div>
					<ul class="items">
						{foreach from=$dag.items item=item name=agendaItems}
							<li {if $smarty.foreach.agendaItems.iteration % 2==1}class="odd"{/if}>
							{if $item instanceof Lid} {* Verjaardag *}
								{icon get="verjaardag"} {$item->getTitel()}
							{elseif $item instanceof Maaltijd}
								{icon get="cup"} <div class="tijd">{$item->getBeginMoment()|date_format:"%R"}</div>
								<a href="/actueel/maaltijden/" title="{$item->getBeschrijving()|escape:'htmlall'}">
									{$item->getTitel()}
								</a>
							{else}
								{if $magBeheren && $item instanceof AgendaItem}
									 <a class="beheren" href="/actueel/agenda/verwijderen/{$item->getItemID()}/" onclick="return confirm('Weet u zeker dat u dit agenda-item wilt verwijderen?');" title="verwijderen">
										{icon get="verwijderen"}
									</a>
									 <a class="beheren" href="/actueel/agenda/bewerken/{$item->getItemID()}/" title="bewerken">
										{icon get="bewerken"}
									</a>
								{/if}
								{if !$item->isHeledag()}
									<div class="tijd">
										{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"}
									</div>
								{/if}
								<span title="{$item->getBeschrijving()|escape:'htmlall'}">{$item->getTitel()}</span>
							{/if}{* end if $item instance of ?? *}
							</li>
						{/foreach}
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
<div class="maandnavigatie">
	<a class="knop" href="{$urlVorige}" style="float: left;" >&laquo; Vorige maand</a>
	<a class="knop" href="{$urlVolgende}" style="float: right;">Volgende maand &raquo;</a>
</div>
