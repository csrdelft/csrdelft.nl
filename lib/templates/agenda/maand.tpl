<h1>Agenda {$datum|date_format:"%B %Y"}</h1>

{$melding}

<table class="agenda maand">
	<a class="knop" href="{$urlVorige}" style="float: left;" >&laquo; Vorige maand</a></td>
	<a class="knop" href="{$urlVolgende}" style="float: right;">Volgende maand &raquo;</a>
	<br /><br style="clear: both;" />
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
		<tr>		
			<th>{$weeknr}</th>
			{foreach from=$dagen key=dagnr item=dag}
				<td class="{if strftime('%U', $dag.datum) == strftime('%U')}dezeweek {/if}{if strftime('%m', $dag.datum) != strftime('%m', $datum)}anderemaand{/if}">
					{if	$magToevoegen}
						<a class="toevoegen" href="/actueel/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}/">{icon get="toevoegen"}</a>
					{/if}	
					{$dagnr}
					{foreach from=$dag.items item=item}
						<hr style="clear: both;" />
						{if $magBeheren && $item instanceof AgendaItem}
							 <a class="beheren" href="/actueel/agenda/verwijderen/{$item->getItemID()}/" onclick="return confirm('Weet u zeker dat u dit agenda-item wilt verwijderen?');">{icon get="verwijderen"}</a>
							 <a class="beheren" href="/actueel/agenda/bewerken/{$item->getItemID()}/">{icon get="bewerken"}</a>
						{/if}
						{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"}<br style="clear: both;" />
						<b>{$item->getTitel()}</b>
						<br />
					{/foreach}
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>