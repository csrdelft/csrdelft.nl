{*
	beheer_maaltijd_repetitie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="repetitie-row-{$repetitie->getMaaltijdRepetitieId()}">
	<td>{strip}
		<a href="{$module}/bewerk/{$repetitie->getMaaltijdRepetitieId()}" title="Maaltijdrepetitie wijzigen" class="knop post popup">{icon get="pencil"}</a>
		<a href="/actueel/taken/corveerepetities/maaltijd/{$repetitie->getMaaltijdRepetitieId()}" title="Corveebeheer maaltijdrepetitie" class="knop get">{icon get="chart_organisation"}</a>
	</td>{/strip}
	<td>{$repetitie->getStandaardTitel()}</td>
	<td>{$repetitie->getDagVanDeWeekText()}</td>
	<td>{$repetitie->getPeriodeInDagenText()}</td>
	<td>{$repetitie->getStandaardTijd()|date_format:"%H:%M"}</td>
	<td>&euro; {$repetitie->getStandaardPrijs()|string_format:"%.2f"}</td>
	<td>{$repetitie->getStandaardLimiet()}</td>
	<td>{if $repetitie->getIsAbonneerbaar()}{icon get="tick" title="Abonneerbaar"}{/if}</td>
	<td>{$repetitie->getAbonnementFilter()}</td>
	<td class="col-del"><a href="{$module}/verwijder/{$repetitie->getMaaltijdRepetitieId()}" title="Maaltijdrepetitie definitief verwijderen" class="knop post confirm">{icon get="cross"}</a></td>
</tr>