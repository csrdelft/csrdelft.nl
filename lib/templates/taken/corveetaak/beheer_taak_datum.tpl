{*
	beheer_taak_datum.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="taak-datum-summary-{$datum}" class="taak-datum-summary taak-datum-{$datum}{if strtotime($datum) < strtotime('-1 day')}{if !isset($show) and !$prullenbak}  taak-datum-oud{/if} taak-oud{/if}"{if isset($show)} style="display: none;"{/if} onclick="taken_toggle_datum('{$datum}');">
	{foreach name=functie from=$perdatum key=fid item=perfunctie}
		{if $smarty.foreach.functie.first}
			{assign var="first" value="true"}
		{/if}
		{foreach name=taken from=$perfunctie item=taak}
			{if $first eq "true"}
				{assign var="first" value="false"}
	<th style="width: 60px; padding: 2px 7px;{if strtotime($datum) >= strtotime('-1 day')} background-color: {cycle values="#F0F0F0,#FAFAFA" advance=false};{/if} color: #000;">
				{if $taak->getMaaltijdId()}
		<a href="/corveebeheer/maaltijd/{$taak->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop get">{icon get="cup_link"}</a>
		<a href="/maaltijdenbeheer/beheer/{$taak->getMaaltijdId()}" title="Wijzig gekoppelde maaltijd" class="knop get">{icon get="cup_edit"}</a>
				{else}
		<div style="display: inline-block; width: 28px;"></div>
					{if $taak->getCorveeRepetitieId()}
		<a href="/corveerepetities/beheer/{$taak->getCorveeRepetitieId()}" title="Wijzig gekoppelde corveerepetitie" class="knop get popup">{icon get="calendar_edit"}</a>
					{/if}
				{/if}
	</th>
	<th colspan="6" style="background-color: {cycle}; color: #000;">
		<div style="display: inline-block; width: 80px; font-weight: normal;">{$taak->getDatum()|date_format:"%a %e %b"}</div>
			{/if}
			{if $smarty.foreach.taken.first}
				{counter assign=count start=0}
		<div style="display: inline-block; width: 70px;">
			<span title="{$taak->getCorveeFunctie()->getNaam()}">
				&nbsp;{$taak->getCorveeFunctie()->getAfkorting()}:&nbsp;
			</span>
			{/if}
			{if $taak->getLidId()}
				{counter}
			{/if}
			{if $smarty.foreach.taken.last}
			<span class="functie-{if $count === $smarty.foreach.taken.total}toegewezen{else}open{/if}" style="background-color: inherit;">
				{$count}/{$smarty.foreach.taken.total}
			</span>
		</div>
			{/if}
		{/foreach}
	{/foreach}
	</th>
</tr>
{include file='taken/corveetaak/beheer_taak_head.tpl' datum=$datum}
{/strip}