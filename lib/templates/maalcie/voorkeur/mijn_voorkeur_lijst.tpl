{*
	mijn_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr>
	{include file='maalcie/voorkeur/mijn_voorkeur_veld.tpl' uid=$voorkeur->getUid() crid=$voorkeur->getCorveeRepetitieId()}
	<td>{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->naam}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getDagVanDeWeekText()}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getPeriodeInDagenText()}</td>
</tr>