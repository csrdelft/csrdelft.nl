{*
	beheer_maaltijd_repetitie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="repetitie-row-{$repetitie->getMaaltijdRepetitieId()}">
	<td>{strip}
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$repetitie->getMaaltijdRepetitieId()}" title="Maaltijdrepetitie wijzigen" class="btn rounded post modal">{icon get="pencil"}</a>
		<a href="/corveerepetities/maaltijd/{$repetitie->getMaaltijdRepetitieId()}" title="Corveebeheer maaltijdrepetitie" class="btn rounded modal">{icon get="chart_organisation"}</a>
	</td>{/strip}
	<td>{$repetitie->getStandaardTitel()}</td>
	<td>{$repetitie->getDagVanDeWeekText()}</td>
	<td>{$repetitie->getPeriodeInDagenText()}</td>
	<td>{$repetitie->getStandaardTijd()|date_format:"%H:%M"}</td>
	<td>&euro; {$repetitie->getStandaardPrijsFloat()|string_format:"%.2f"}</td>
	<td>{$repetitie->getStandaardLimiet()}</td>
	<td>{if $repetitie->getIsAbonneerbaar()}{icon get="tick" title="Abonneerbaar"}{/if}</td>
	<td>{$repetitie->getAbonnementFilter()}</td>
	<td class="col-del"><a href="{$smarty.const.maalcieUrl}/verwijder/{$repetitie->getMaaltijdRepetitieId()}" title="Maaltijdrepetitie definitief verwijderen" class="btn rounded post confirm">{icon get="cross"}</a></td>
</tr>