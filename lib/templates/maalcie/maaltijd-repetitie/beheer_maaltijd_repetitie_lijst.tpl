{*
	beheer_maaltijd_repetitie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="repetitie-row-{$repetitie->mlt_repetitie_id}">
	<td>{strip}
		<a href="/maaltijden/repetities/bewerk/{$repetitie->mlt_repetitie_id}" title="Maaltijdrepetitie wijzigen" class="btn post popup">{icon get="pencil"}</a>
		<a href="/corvee/repetities/maaltijd/{$repetitie->mlt_repetitie_id}" title="Corveebeheer maaltijdrepetitie" class="btn popup">{icon get="chart_organisation"}</a>
	</td>{/strip}
	<td>{$repetitie->standaard_titel}</td>
	<td>{$repetitie->getDagVanDeWeekText()}</td>
	<td>{$repetitie->getPeriodeInDagenText()}</td>
	<td>{$repetitie->standaard_tijd|date_format:"%H:%M"}</td>
	<td>&euro; {$repetitie->getStandaardPrijsFloat()|string_format:"%.2f"}</td>
	<td>{$repetitie->standaard_limiet}</td>
	<td>{if $repetitie->abonneerbaar}{icon get="tick" title="Abonneerbaar"}{/if}</td>
	<td>{$repetitie->abonnement_filter}</td>
	<td class="col-del"><a href="/maaltijden/repetities/verwijder/{$repetitie->mlt_repetitie_id}" title="Maaltijdrepetitie definitief verwijderen" class="btn post confirm">{icon get="cross"}</a></td>
</tr>
