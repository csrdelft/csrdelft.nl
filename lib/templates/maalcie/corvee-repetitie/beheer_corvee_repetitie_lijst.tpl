{*
	beheer_corvee_repetitie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="repetitie-row-{$repetitie->crv_repetitie_id}">
	<td>{strip}
		<a href="/corvee/repetities/bewerk/{$repetitie->crv_repetitie_id}" title="Corveerepetitie wijzigen" class="btn post popup">{icon get="pencil"}</a>
		<a href="/corvee/functies/{$repetitie->functie_id}" title="Wijzig onderliggende functie" class="btn popup">{icon get="cog_edit"}</a>
{if !isset($maaltijdrepetitie) and $repetitie->mlt_repetitie_id}
		<a href="/corvee/repetities/maaltijd/{$repetitie->mlt_repetitie_id}" title="Corveebeheer maaltijdrepetitie" class="btn">{icon get="calendar_link"}</a>
{/if}
	</td>{/strip}
	<td>{$repetitie->getCorveeFunctie()->naam}</td>
	<td>{$repetitie->getDagVanDeWeekText()}</td>
	<td>{$repetitie->getPeriodeInDagenText()}</td>
	<td>{if $repetitie->voorkeurbaar}{icon get="tick" title="Voorkeurbaar"}{/if}</td>
	<td>{$repetitie->standaard_punten}</td>
	<td>{$repetitie->standaard_aantal}</td>
	<td class="col-del"><a href="/corvee/repetities/verwijder/{$repetitie->crv_repetitie_id}" title="Corveerepetitie definitief verwijderen" class="btn post confirm">{icon get="cross"}</a></td>
</tr>
