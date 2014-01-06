{*
	beheer_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{foreach name=loop from=$voorkeuren item=voorkeur}
	{if $voorkeur}
		{if $smarty.foreach.loop.first}
<tr id="voorkeur-row-{$voorkeur->getLid()->getUid()}">
	<td>{$voorkeur->getLid()->getNaamLink($globals.weergave_ledennamen_beheer, $globals.weergave_ledennamen)}</td>
		{/if}
		{include file='taken/voorkeur/beheer_voorkeur_veld.tpl' voorkeur=$voorkeur crid=$voorkeur->getCorveeRepetitieId() uid=$voorkeur->getLidId()}
		{if $smarty.foreach.loop.last}
</tr>
		{/if}
	{/if}
{/foreach}