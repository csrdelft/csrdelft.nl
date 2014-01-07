{*
	corvee_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if $toonverleden}
<div style="float: right;">
	<a href="{$GLOBALS.taken_module}/verleden" title="Taken in het verleden tonen" class="knop get">{icon get="time"} Toon verleden</a>
</div>
{/if}
<table id="taken-tabel" class="taken-tabel">
	<thead>
		<tr>
			<th>Week</th>
			<th>Datum</th>
			<th>Functie</th>
{if !isset($mijn)}<th colspan="10">Corveeër(s)</th>{/if}
		</tr>
	</thead>
	<tbody>
{foreach from=$rooster key=week item=datums}
{foreach name=week from=$datums key=datum item=functies}
	{if $smarty.foreach.week.first}
		{cycle name="firstOfWeek" assign="firstOfWeek" values="true,false"}
	{/if}
{foreach name=datum from=$functies item=taken}
	{if $smarty.foreach.datum.first}
		{cycle name="firstOfDatum" assign="firstOfDatum" values="true,false"}
	{/if}
		<tr>
	{foreach name=taak from=$taken item=taak}
		{if $firstOfWeek eq 'true'}
			{cycle name="firstOfWeek" assign="firstOfWeek"}
			{cycle name="weekColor" assign="weekColor" values="#EBEBEB,#FAFAFA"}
			<td rowspan="{$smarty.foreach.datum.total}"{if !isset($mijn)} style="background-color: {$weekColor};"{/if}><nobr>{$datum|date_format:"%W"}</nobr></td>
		{elseif $firstOfDatum eq 'true'}
			<td rowspan="{$smarty.foreach.datum.total}"{if !isset($mijn)} style="background-color: {$weekColor};"{/if}></td>
		{/if}
		{if $firstOfDatum eq 'true'}
			{cycle name="firstOfDatum" assign="firstOfDatum"}
			{cycle name="datumColor" assign="datumColor" values="#EBEBEB,#FAFAFA"}
			<td rowspan="{$smarty.foreach.datum.total}"{if !isset($mijn)} style="background-color: {$datumColor};"{/if}><nobr>{$datum|date_format:"%a %e %b"}</nobr></td>
		{/if}
		{if $smarty.foreach.taak.first}
			<td><nobr>{$taak->getCorveeFunctie()->getNaam()}</nobr></td>
		{/if}
		{if !isset($mijn)}
				<td{if $smarty.foreach.taak.last} colspan="10"{else} style="width: 150px;"
			{/if}{if $taak->getLidId()}{if $loginlid->getUid() === $taak->getLidId()} class="taak-self">
					<a href="/communicatie/forum/zoeken.php?zoeken=corveedraad" title="Ruilen" class="knop" style="margin-right:10px;">{icon get="arrow_switch"}</a>
				{else}>
				{/if}
				{$taak->getLid()->getNaamLink($GLOBALS.weergave_ledennamen_corveerooster, $GLOBALS.weergave_ledennamen)}
			{else} class="taak-grijs"><i>vacature</i>
			{/if}</td>
		{/if}
	{/foreach}
		</tr>
{/foreach}
{/foreach}
{/foreach}
	</tbody>
</table>