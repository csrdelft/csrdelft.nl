{*
	mijn_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr>
	{include file='taken/voorkeur/mijn_voorkeur_veld.tpl' uid=$voorkeur->getLidId() crid=$voorkeur->getCorveeRepetitieId()}
	<td>{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->naam}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getDagVanDeWeekText()}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getPeriodeInDagenText()}</td>
</tr>