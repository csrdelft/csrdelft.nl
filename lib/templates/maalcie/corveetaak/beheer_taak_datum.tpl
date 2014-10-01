{*
	beheer_taak_datum.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="taak-datum-summary-{$datum}"
	class="taak-datum-summary taak-datum-{$datum}
{if strtotime($datum) < strtotime('-1 day')}
	{if !isset($show) and !$prullenbak} taak-datum-oud
	{/if} taak-oud
{/if}
{if isset($show)} verborgen
{/if}" onclick="taken_toggle_datum('{$datum}');">
	<th colspan="7" style="background-color: {cycle values="#f5f5f5,#FAFAFA"}; color: #000;">
	{foreach name=functie from=$perdatum key=fid item=perfunctie}
		{foreach name=taken from=$perfunctie item=taak}
			{if $smarty.foreach.taken.first}{* eerste taak van functie: reset ingedeeld-teller *}
				{counter assign=count start=0}
				{if $smarty.foreach.functie.first}
		<div class="inline" style="width: 80px; font-weight: normal;">{$taak->getDatum()|date_format:"%a %e %b"}</div>
				{/if}
		<div class="inline" style="width: 70px;">
			<span title="{$taak->getCorveeFunctie()->naam}">
				&nbsp;{$taak->getCorveeFunctie()->afkorting}:&nbsp;
			</span>
			{/if}
			{if $taak->getUid()}{* ingedeelde taak van functie: teller++ *}
				{counter}
			{/if}
			{if $smarty.foreach.taken.last}{* laatste taak van functie: toon ingedeeld-teller en totaal aantal taken van deze functie *}
			<span class="functie-{if $count === $smarty.foreach.taken.total}toegewezen{else}open{/if}" style="background-color: inherit;">
				{$count}/{$smarty.foreach.taken.total}
			</span>
		</div>
			{/if}
		{/foreach}
	{/foreach}
	</th>
</tr>
{include file='maalcie/corveetaak/beheer_taak_head.tpl' datum=$datum}
{/strip}