{*
	mijn_abonnement_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr>
	{include file='maalcie/abonnement/mijn_abonnement_veld.tpl' uid=$abonnement->uid mrid=$abonnement->mlt_repetitie_id}
	<td>{$abonnement->maaltijd_repetitie->standaard_titel}</td>
	<td>{$abonnement->maaltijd_repetitie->getDagVanDeWeekText()}</td>
	<td>{$abonnement->maaltijd_repetitie->getPeriodeInDagenText()}</td>
</tr>