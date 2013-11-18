{*
	mijn_voorkeur_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr>
	{include file='taken/voorkeur/mijn_voorkeur_veld.tpl' uid=$voorkeur->getLidId() crid=$voorkeur->getCorveeRepetitieId()}
	<td>{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->getNaam()}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getDagVanDeWeekTimestamp()|date_format:"%A"}</td>
	<td>{$voorkeur->getCorveeRepetitie()->getPeriodeInDagenText()}</td>
	<td title="{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->getOmschrijving()}">{$voorkeur->getCorveeRepetitie()->getCorveeFunctie()->getOmschrijving()|truncate:30:"...":true}</td>
</tr>