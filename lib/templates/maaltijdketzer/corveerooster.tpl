{assign var='actief' value='corveerooster'}
{include file='maaltijdketzer/menu.tpl'}

<h1>Corveerooster</h1>

<table class="maaltijden">
	<tr>
		<th colspan="2">Week</th>
		<th style="min-width: 80px;">Datum + Opm.</th>
		<th>Tafelpraeses</th>
		<th>Koks</th>
		<th>Afwassers</th>
		<th>Theedoek</th>
		<th>Frituur</th>
		<th>Afzuigkap</th>
		<th>Keuken</th>
		<th>Lichte Klus</th>
		<th>Zware Klus</th>
	</tr>
	{foreach from=$maal.maaltijden item=maaltijd}
		<tr {if $maaltijd.datum<=$smarty.now}class="old"{/if} style="background-color: {cycle values="#e9e9e9, #fff"}">
			<td>{$maaltijd.datum|date_format:$datumWeek}</td>
			<td>{$maaltijd.datum|date_format:$datumWeekdag}</td>
			<td><a href="{if $loginlid->hasPermission('P_MAAL_MOD')}/actueel/maaltijden/corveebeheer/{if $maaltijd.datum>=$smarty.now}takenbewerk/{$maaltijd.id}#corveetakenFormulier{else}puntenbewerk/{$maaltijd.id}#corveepuntenFormulier{/if}{else}/actueel/maaltijden/{/if}" title="{$maaltijd.tekst|truncate:20|escape:'html'}">{$maaltijd.datum|date_format:$datumVol}</a></td>
			<td>{if $maaltijd.tp!=''}<span class="{if $maaltijd.tp==$liduid}mijzelf{/if}">{$maaltijd.tp|csrnaam}</span>{else}{if $maaltijd.type=='corvee'}{else}...{/if}{/if}</td>
			<td>
				{section name=kwalikoks loop=$maaltijd.kwalikoks}
					{assign var='it' value=$smarty.section.kwalikoks.iteration-1}
					{assign var='kwalikok' value=$maaltijd.taken.kwalikoks.$it}
					{if $kwalikok!=''}<span class="{if $kwalikok==$liduid}mijzelf{/if}">{$kwalikok|csrnaam}</span>{else}...{/if} <span title="Kwalikok">(k)</span><br />
				{/section}
				{section name=koks loop=$maaltijd.koks}
					{assign var='it' value=$smarty.section.koks.iteration-1}
					{assign var='kok' value=$maaltijd.taken.koks.$it}
					{if $kok!=''}<span class="{if $kok==$liduid}mijzelf{/if}">{$kok|csrnaam}</span>{else}...{/if}<br />
				{/section}
			</td>
			<td>
				{section name=kwaliafwassers loop=$maaltijd.kwaliafwassers}
					{assign var='it' value=$smarty.section.kwaliafwassers.iteration-1}
					{assign var='kwaliafwasser' value=$maaltijd.taken.kwaliafwassers.$it}
					{if $kwaliafwasser!=''}<span class="{if $kwaliafwasser==$liduid}mijzelf{/if}">{$kwaliafwasser|csrnaam}</span>{else}...{/if} <span title="Kwaliafwasser">(k)</span><br />
				{/section}
				{section name=afwassers loop=$maaltijd.afwassers}
					{assign var='it' value=$smarty.section.afwassers.iteration-1}
					{assign var='afwasser' value=$maaltijd.taken.afwassers.$it}
					{if $afwasser!=''}<span class="{if $afwasser==$liduid}mijzelf{/if}">{$afwasser|csrnaam}</span>{else}...{/if}<br />
				{/section}
			</td>
			<td>
				{section name=theedoeken loop=$maaltijd.theedoeken}
					{assign var='it' value=$smarty.section.theedoeken.iteration-1}
					{assign var='theedoek' value=$maaltijd.taken.theedoeken.$it}
					{if $theedoek!=''}<span class="{if $theedoek==$liduid}mijzelf{/if}">{$theedoek|csrnaam}</span>{else}...{/if}<br />
				{/section}
			</td>
			<td>
				{section name=schoonmaken_frituur loop=$maaltijd.schoonmaken_frituur}
					{assign var='it' value=$smarty.section.schoonmaken_frituur.iteration-1}
					{assign var='frituur' value=$maaltijd.taken.schoonmaken_frituur.$it}
					{if $frituur!=''}<span class="{if $frituur==$liduid}mijzelf{/if}">{$frituur|csrnaam}</span>{/if}<br />
				{/section}
			</td>
			<td>
				{section name=schoonmaken_afzuigkap loop=$maaltijd.schoonmaken_afzuigkap}
					{assign var='it' value=$smarty.section.schoonmaken_afzuigkap.iteration-1}
					{assign var='afzuigkap' value=$maaltijd.taken.schoonmaken_afzuigkap.$it}
					{if $afzuigkap!=''}<span class="{if $afzuigkap==$liduid}mijzelf{/if}">{$afzuigkap|csrnaam}</span>{/if}<br />
				{/section}
			</td>
			<td>
				{section name=schoonmaken_keuken loop=$maaltijd.schoonmaken_keuken}
					{assign var='it' value=$smarty.section.schoonmaken_keuken.iteration-1}
					{assign var='keuken' value=$maaltijd.taken.schoonmaken_keuken.$it}
					{if $keuken!=''}<span class="{if $keuken==$liduid}mijzelf{/if}">{$keuken|csrnaam}</span>{/if}<br />
				{/section}
			</td>
			<td>
				{section name=klussen_licht loop=$maaltijd.klussen_licht}
					{assign var='it' value=$smarty.section.klussen_licht.iteration-1}
					{assign var='lichteklus' value=$maaltijd.taken.klussen_licht.$it}
					{if $lichteklus!=''}<span class="{if $lichteklus==$liduid}mijzelf{/if}">{$lichteklus|csrnaam}</span>{/if}<br />
				{/section}
			</td>
			<td>
				{section name=klussen_zwaar loop=$maaltijd.klussen_zwaar}
					{assign var='it' value=$smarty.section.klussen_zwaar.iteration-1}
					{assign var='zwareklus' value=$maaltijd.taken.klussen_zwaar.$it}
					{if $zwareklus!=''}<span class="{if $zwareklus==$liduid}mijzelf{/if}">{$zwareklus|csrnaam}</span>{/if}<br />
				{/section}
			</td>
		</tr>
	{/foreach}
</table>
