{*
	corvee_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
	{if $toonverleden}
		<div style="float: right;">
			<a href="{Instellingen::get('taken', 'url')}/verleden" title="Taken in het verleden tonen" class="knop">{icon get="time"} Toon verleden</a>
		</div>
	{/if}
	<table id="taken-tabel" class="taken-tabel">
		<thead>
			<tr>
				<th>Week</th>
				<th>Datum</th>
				<th>Functie</th>
				{if !isset($mijn)}<th colspan="2">Corveeër(s)</th>{/if}
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
					{if array_key_exists(0, $taken)}
				<td><nobr>{$taken[0]->getCorveeFunctie()->naam}</nobr></td>
				{/if}
				{if !isset($mijn)}
			<td>
				{table_foreach from=$taken inner=rows item=taak table_attr='class="taken-rooster"' cols=2}
			</td>
			{if $taak->getLidId()}
				{if $taak->getLidId() === LoginLid::instance()->getUid()} 
					{assign var="class" value="taak-self"}
				{else}
					{assign var="class" value=""}
				{/if}
			{else}
				{assign var="class" value="taak-grijs"}
			{/if}
			<td class="taak {$class}">
				{if $taak->getLidId()}
					{if $taak->getLidId() === LoginLid::instance()->getUid()} 
						{* icon get="arrow_switch" title="Ruilen" *}
					{/if}
					{$taak->getLid()->getNaamLink(Instellingen::get('corvee', 'weergave_ledennamen_corveerooster'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}
				{else}
					<i>vacature</i>
				{/if}
			<td>
				{/table_foreach}
			</td>
		{/if}
	</tr>
{/foreach}
{/foreach}
{/foreach}
</tbody>
</table>
{/strip}