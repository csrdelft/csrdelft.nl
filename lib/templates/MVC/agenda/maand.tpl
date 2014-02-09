{$view->getMelding()}
{capture name='navlinks'}
	<div class="maandnavigatie">
		<h1>{$datum|date_format:"%B %Y"}</h1>
		<a class="knop" href="{$urlVorige}" style="float: left;" >&laquo; Vorige maand</a>
		<a class="knop" href="{$urlVolgende}" style="float: right;">Volgende maand &raquo;</a>
	</div>
{/capture}
{$smarty.capture.navlinks}
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
							<a class="toevoegen" href="/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}/"
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
									{elseif $item|is_a:'Maaltijd'}
										{icon get="cup"} <div class="tijd">{$item->getBeginMoment()|date_format:"%R"}</div>
										<a href="/maaltijden" title="{$item->getBeschrijving()}">
											{$item->getTitel()}
										</a>
									{elseif $item|is_a:'CorveeTaak'}
										{icon get="paintcan"}
										<a href="/corveerooster" title="{$item->getBeschrijving()}">
											{$item->getTitel()}
										</a>
									{else}
										{if $magBeheren && $item instanceof AgendaItem}
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
								{/if}{* end if $item instance of ?? *}
							</li>
						{/foreach}
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{$smarty.capture.navlinks}
<div id="ical"><a href="/agenda/icalendar/" title="ICalender export (Google calendar)"><img src="{$CSR_PICS}knopjes/ical.gif" /></a></div>