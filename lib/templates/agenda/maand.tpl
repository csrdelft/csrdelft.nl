{getMelding()}
{capture name='navlinks'}
	<div class="maandnavigatie">
		<a class="btn float-left" href="{$urlVorige}">&laquo; {$prevMaand}</a>
		<a class="btn float-right" href="{$urlVolgende}">{$nextMaand} &raquo;</a>
		<h1>{$datum|date_format:"%B %Y"}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
<table class="agenda" id="maand">
	<tr>
		<th class="weeknr"></th>
		<th>Zondag</th>
		<th>Maandag</th>
		<th>Dinsdag</th>
		<th>Woensdag</th>
		<th>Donderdag</th>
		<th>Vrijdag</th>
		<th>Zaterdag</th>
	</tr>
	{foreach from=$weken key=weeknr item=dagen}
		{foreach from=$dagen key=dagnr item=dag name=dagen}
			{if $smarty.foreach.dagen.first}
				<tr {if strftime('%U', $dag.datum) == strftime('%U')}id="dezeweek"{/if}>
					<th>{$weeknr}</th>
					{/if}
				<td id="dag-{$dag.datum|date_format:"%Y-%m-%d"}" class="dag {if strftime('%m', $dag.datum) != strftime('%m', $datum)}anderemaand{/if}{if date('d-m', $dag.datum)==date('d-m')} vandaag{/if}">
					<div class="meta">
						{if LoginModel::mag('P_AGENDA_ADD,verticaleleider')}
							<a href="/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}" class="toevoegen post popup" title="Agenda-item toevoegen">{icon get="add"}</a>
						{/if}
						{$dagnr}
					</div>
					<ul id="items-{$dag.datum|date_format:"%Y-%m-%d"}" class="items">
						{foreach from=$dag.items item=item}
							{if $item instanceof AgendaItem}
								{include file='agenda/maand_item.tpl'}
							{elseif $item instanceof Profiel}
								<li>
									{icon get="verjaardag"}
									{$item->getLink()}
								</li>
							{elseif $item instanceof Bijbelrooster}
								<li>
									{icon get="book_open"}
									{$item->getLink(true)}
								</li>
							{elseif $item instanceof Maaltijd}
								<li>
									<img src="/plaetjes/maalcie/cutlery.png" width="16" height="16" alt="cutlery" class="icon" />
									<div class="tijd">
										{$item->getBeginMoment()|date_format:"%R"} - {$item->getEindMoment()|date_format:"%R"}
									</div>
									<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								</li>
							{elseif $item instanceof CorveeTaak}
								<li>
									{if $item->getCorveeFunctie()->naam|stristr:"klus"}
										<img src="/plaetjes/maalcie/drill.png" width="16" height="16" alt="drill" class="icon" />
									{else}
										{icon get="paintcan"}
									{/if}
									<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								</li>
							{elseif $item instanceof Agendeerbaar}
								<li>
									{if !$item->isHeledag()}
										<div class="tijd">
											{$item->getBeginMoment()|date_format:"%R"}
											{if $item->getBeginMoment() !== $item->getEindMoment() AND date('H:i', $item->getEindMoment()) !== '00:00'}
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
							{/if}
						{/foreach}
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{$smarty.capture.navlinks}
<div id="ical">
	{if LoginModel::getAccount()->hasPrivateToken()}
		<a name="ICAL" href="{LoginModel::getAccount()->getICalLink()}"{if LoginModel::mag('P_LOGGED_IN')} title="Persoonlijke ICalender feed&#013;Nieuwe aanvragen kan op je profiel"{/if}>
		{else}
			<a name="ICAL" href="/profiel/{LoginModel::getUid()}#tokenaanvragen" title="Persoonlijke ICalender feed aanvragen">
			{/if}
			<img src="/plaetjes/knopjes/ical.gif" alt="ICAL" /></a>
</div>