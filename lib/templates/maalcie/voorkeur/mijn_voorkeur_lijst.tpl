{*
	mijn_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr>
	{include file='maalcie/voorkeur/mijn_voorkeur_veld.tpl' uid=$voorkeur->uid crid=$voorkeur->crv_repetitie_id}
	<td>{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->naam}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getDagVanDeWeekText()}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getPeriodeInDagenText()}</td>
</tr>