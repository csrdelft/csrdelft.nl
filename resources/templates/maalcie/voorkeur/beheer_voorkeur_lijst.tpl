{*
	beheer_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{foreach name=loop from=$voorkeuren item=voorkeur}
	{if $voorkeur}
		{if $smarty.foreach.loop.first}
<tr id="voorkeur-row-{$voorkeur->getVanUid()}">
	<td>{CsrDelft\model\ProfielModel::getLink($voorkeur->getVanUid(),CsrDelft\model\InstellingenModel::get('corvee', 'weergave_ledennamen_beheer'))}</td>
		{/if}
		{include file='maalcie/voorkeur/beheer_voorkeur_veld.tpl' voorkeur=$voorkeur crid=$voorkeur->crv_repetitie_id uid=$voorkeur->uid}
		{if $smarty.foreach.loop.last}
</tr>
		{/if}
	{/if}
{/foreach}