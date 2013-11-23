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
			<th style="vertical-align: bottom;">Lid</th>
		{foreach from=$repetities item=repetitie}
			<th style="text-align: center;">{strip}
				<a href="/actueel/taken/corveerepetities/beheer/{$repetitie->getCorveeRepetitieId()}" title="Wijzig corveerepetitie" class="knop get">{icon get="calendar_edit"}</a>
				<div style="display: inline-block; vertical-align: bottom; width: 30px; height: 150px;">
					<div class="vertical" style="position: relative; top: 120px; font-weight: normal;">
						<nobr>{$repetitie->getCorveeFunctie()->getNaam()}</nobr>
						<br /><nobr>op {$repetitie->getDagVanDeWeekText()}</nobr>
					</div>
				</div>
			</th>{/strip}
		{/foreach}
		</tr>
	</thead>
	<tbody>
	{/if}
	{include file='taken/voorkeur/beheer_voorkeur_lijst.tpl' voorkeuren=$voorkeuren}
{/foreach}
	</tbody>
</table>