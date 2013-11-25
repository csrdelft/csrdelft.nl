{*
	beheer_taak_datum.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<thead>
{if !isset($maaltijd)}
	<tr class="taak-datum-{$datum}{if strtotime($datum) < strtotime('-1 day')} taak-oud{/if}" onclick="toggle_taken_datum('{$datum}');">
	{foreach name=functie from=$perdatum key=fid item=perfunctie}
		{if $smarty.foreach.functie.first}
			{assign var="first" value="true"}
		{/if}
		{foreach name=taken from=$perfunctie item=taak}
			{if $first eq "true"}
			{assign var="first" value="false"}
			{assign var="mid" value=$taak->getMaaltijdId()}
		<th style="width: 60px; padding: 2px 7px;">
				{if $taak->getMaaltijdId()}
			<a href="/corveebeheer/maaltijd/{$taak->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop get">{icon get="cup_link"}</a>
			<a href="/maaltijdenbeheer/beheer/{$taak->getMaaltijdId()}" title="Wijzig gekoppelde maaltijd" class="knop get">{icon get="cup_edit"}</a>
				{/if}
		</th>
		<th colspan="6">
			<table>
				<tr>
			{/if}
			{if $smarty.foreach.taken.first}
				{counter assign=count start=0}
					<th>
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
					</th>

			{/if}
		{/foreach}
	{/foreach}
				</tr>
			</table>
		</th>
	</tr>
{/if}
</thead>
{include file='taken/corveetaak/beheer_taak_head.tpl' prullenbak=$prullenbak datum=$datum mid=$mid}
{/strip}