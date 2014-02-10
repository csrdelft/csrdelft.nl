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
							<a class="toevoegen get popup" href="/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}/" title="Item toevoegen">
								{icon get="toevoegen"}
							</a>
						{/if}
						{$dagnr}
					</div>
					<ul id="{$dag.datum}-items" class="items">
						{foreach from=$dag.items item=item}
							<li>
								{if $item instanceof Lid}
									{icon get="verjaardag"} {$item->getTitel()}
								{elseif $item instanceof Maaltijd}
									{icon get="cup"} <div class="tijd">{$item->getBeginMoment()|date_format:"%R"}</div>
									<a href="/maaltijden" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								{elseif $item instanceof CorveeTaak}
									{icon get="paintcan"}
									<a href="/corveerooster" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								{elseif $item instanceof AgendaItem}
									{include file='MVC/agenda/maand_item.tpl'}
								{/if}
							{/foreach}
						</li>
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{$smarty.capture.navlinks}
<div id="ical"><a href="/agenda/icalendar/" title="ICalender export (Google calendar)"><img src="{$CSR_PICS}knopjes/ical.gif" /></a></div>