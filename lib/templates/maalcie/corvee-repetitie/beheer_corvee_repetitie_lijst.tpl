{*
	beheer_corvee_repetitie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="repetitie-row-{$repetitie->getCorveeRepetitieId()}">
	<td>{strip}
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$repetitie->getCorveeRepetitieId()}" title="Corveerepetitie wijzigen" class="btn post popup">{icon get="pencil"}</a>
		<a href="/corveefuncties/beheer/{$repetitie->getFunctieId()}" title="Wijzig onderliggende functie" class="btn modal">{icon get="cog_edit"}</a>
{if !isset($maaltijdrepetitie) and $repetitie->getMaaltijdRepetitieId()}
		<a href="{$smarty.const.maalcieUrl}/maaltijd/{$repetitie->getMaaltijdRepetitieId()}" title="Corveebeheer maaltijdrepetitie" class="btn rounded">{icon get="calendar_link"}</a>
{/if}
	</td>{/strip}
	<td>{$repetitie->getCorveeFunctie()->naam}</td>
	<td>{$repetitie->getDagVanDeWeekText()}</td>
	<td>{$repetitie->getPeriodeInDagenText()}</td>
	<td>{if $repetitie->getIsVoorkeurbaar()}{icon get="tick" title="Voorkeurbaar"}{/if}</td>
	<td>{$repetitie->getStandaardPunten()}</td>
	<td>{$repetitie->getStandaardAantal()}</td>
	<td class="col-del"><a href="{$smarty.const.maalcieUrl}/verwijder/{$repetitie->getCorveeRepetitieId()}" title="Corveerepetitie definitief verwijderen" class="btn post confirm">{icon get="cross"}</a></td>
</tr>