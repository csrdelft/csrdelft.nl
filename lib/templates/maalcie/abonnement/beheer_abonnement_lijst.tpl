{*
	beheer_abonnement_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="abonnement-row-{$uid}">
	{foreach name=loop from=$abonnementen item=abonnement}
		{if $smarty.foreach.loop.first}
			<td>{Lid::naamLink($abonnement->getVanLidId(), Instellingen::get('maaltijden', 'weergave_ledennamen_beheer'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'))}</td>
		{/if}
		{if $abonnement->getMaaltijdRepetitieId() and $abonnement->getMaaltijdRepetitie()->getIsAbonneerbaar()}
			{include file='maalcie/abonnement/beheer_abonnement_veld.tpl' abonnement=$abonnement lidid=$abonnement->getLidId() uid=$uid}
		{else}
			<td></td>
		{/if}
	{/foreach}
</tr>