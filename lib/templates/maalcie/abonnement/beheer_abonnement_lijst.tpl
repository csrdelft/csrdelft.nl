{*
	beheer_abonnement_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="abonnement-row-{$vanuid}">
	{foreach name=loop from=$abonnementen item=abonnement}
		{if $smarty.foreach.loop.first}
			<td>{CsrDelft\model\ProfielModel::getLink($vanuid,instelling('maaltijden', 'weergave_ledennamen_beheer'))}</td>
		{/if}
		{if $abonnement->maaltijd_repetitie and $abonnement->maaltijd_repetitie->abonneerbaar}
			{include file='maalcie/abonnement/beheer_abonnement_veld.tpl' uid=$abonnement->uid}
		{else}
			<td></td>
		{/if}
	{/foreach}
</tr>
