{*
	beheer_voorkeuren.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u voor alle leden de voorkeuren beheren.
</p>
<table id="taken-tabel" class="taken-tabel">
{foreach name=tabel from=$matrix item=voorkeuren}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
	<thead>
		<tr>
			<th>Lid</th>
		{foreach from=$repetities item=repetitie}
			<th>
				{$repetitie->getCorveeFunctie()->getNaam()} op {$repetitie->getDagVanDeWeekText()}
				&nbsp;<a href="/actueel/taken/corveerepetities/beheer/{$repetitie->getCorveeRepetitieId()}" title="Wijzig corveerepetitie" class="knop get">{icon get="calendar_edit"}</a>
			</th>
		{/foreach}
		</tr>
	</thead>
	<tbody>
	{/if}
	{include file='taken/voorkeur/beheer_voorkeur_lijst.tpl' voorkeuren=$voorkeuren}
{/foreach}
	</tbody>
</table>