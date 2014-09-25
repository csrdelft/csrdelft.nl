{*
	beheer_voorkeuren.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u voor alle leden de voorkeuren beheren.
</p>
<table id="maalcie-tabel" class="maalcie-tabel">
{foreach name=tabel from=$matrix item=voorkeuren}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
	<thead>
		<tr>
			<th style="vertical-align: bottom;">Lid</th>
		{foreach from=$repetities item=repetitie}
			<th style="width: 30px; background-color: {cycle values="#f5f5f5,#FAFAFA"}; color: #000;">{strip}
				<div style="width: 28px;">
					<a href="/corveerepetities/beheer/{$repetitie->getCorveeRepetitieId()}" title="Wijzig corveerepetitie" class="knop popup">
						{icon get="calendar_edit"}
					</a>
				</div>
				<div style="width: 26px; height: 160px;">
					<div class="vertical" style="font-weight: normal; position: relative; top: 130px;">
						<nobr>{$repetitie->getCorveeFunctie()->naam}</nobr>
						<br /><nobr>op {$repetitie->getDagVanDeWeekText()}</nobr>
					</div>
				</div>
			</th>{/strip}
		{/foreach}
		</tr>
	</thead>
	<tbody>
	{/if}
	{include file='maalcie/voorkeur/beheer_voorkeur_lijst.tpl' voorkeuren=$voorkeuren}
{/foreach}
	</tbody>
</table>