{SimpleHtml::getMelding()}
{capture name='navlinks'}
	<div class="maandnavigatie">
		<a class="knop float-left" href="{$urlVorige}">&laquo; {$prevMaand}</a>
		<h1>{$datum|date_format:"%B %Y"}</h1>
		<a class="knop float-right" href="{$urlVolgende}">{$nextMaand} &raquo;</a>
	</div>
{/capture}
{$smarty.capture.navlinks}
<table class="agenda" id="maand">
	<tr>
		<th style="width: 25px;"></th>
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
						{if LoginModel::mag('P_AGENDA_ADD')}
							<a href="/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}" class="toevoegen post modal" title="Agenda-item toevoegen">{icon get="add"}</a>
						{/if}
						{$dagnr}
					</div>
					<ul id="items-{$dag.datum|date_format:"%Y-%m-%d"}" class="items">
						{foreach from=$dag.items item=item}
							{if $item instanceof Lid}
								<li>
									{icon get="verjaardag"}
									{$item->getLink()}
								</li>
							{elseif $item instanceof Maaltijd}
								<li>
									{icon get="cup"} <div class="tijd">{$item->getBeginMoment()|date_format:"%R"}</div>
									<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								</li>
							{elseif $item instanceof CorveeTaak}
								<li>
									{icon get="paintcan"}
									<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">
										{$item->getTitel()}
									</a>
								</li>
							{elseif $item instanceof Bijbelrooster}
								<li>
									{icon get="book_open"}
									{$item->getLink(true)}
								</li>
							{elseif $item instanceof AgendaItem}
								{include file='MVC/agenda/maand_item.tpl'}
							{/if}
						{/foreach}
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{$smarty.capture.navlinks}
<div id="ical"><a name="ICAL" href="{LoginModel::instance()->getLid()->getICalLink()}" title="ICalender feed"><img src="{$CSR_PICS}/knopjes/ical.gif" alt="ICAL" /></a></div>