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
						{toegang P_AGENDA_ADD}
							<a href="/agenda/toevoegen/{$dag.datum|date_format:"%Y-%m-%d"}" class="beheren post popup" title="Agenda-item toevoegen">{icon get="add"}</a>
						{/toegang}
						{$dagnr}
					</div>
					<ul id="items-{$dag.datum|date_format:"%Y-%m-%d"}" class="items">
						{foreach from=$dag.items item=item}
							{include file='agenda/maand_item.tpl'}
						{/foreach}
					</ul>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{$smarty.capture.navlinks}
<div id="ICAL" class="input-group" title="Houd deze url privé!&#013;Nieuwe aanvragen: zie je profiel">
	<div class="input-group-prepend">
		<span class="input-group-text"><img src="/images/ical.gif" alt="ICAL" /></span>
	</div>
{if CsrDelft\model\security\LoginModel::getUid() == 'x999' OR CsrDelft\model\security\LoginModel::getAccount()->hasPrivateToken()}
	<input class="form-control" type="text" value="{CsrDelft\model\security\LoginModel::getAccount()->getICalLink()}" size="35" onclick="this.setSelectionRange(0, this.value.length);" readonly />
{else}
	<a class="input-group-text" href="/profiel/{CsrDelft\model\security\LoginModel::getUid()}#tokenaanvragen">Privé url aanvragen</a>
{/if}
</div>
