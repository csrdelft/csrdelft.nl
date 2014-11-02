{*
	beheer_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}"
{if strtotime($maaltijd->getDatum()) < strtotime('-1 day')} class="
	{if !$prullenbak} taak-maaltijd-oud{/if} taak-oud"
{/if}>
	<td>
{if $maaltijd->getArchief() !== null}
		<div class="inline" style="width: 28px;">{icon get="compress" title="Maaltijd is gearchiveerd"}</div>
{elseif $maaltijd->getIsVerwijderd()}
		<a href="{maalcieUrl}/herstel/{$maaltijd->getMaaltijdId()}" title="Maaltijd herstellen" class="knop rounded post">{icon get="arrow_undo"}</a>
{else}
		<a href="{maalcieUrl}/bewerk/{$maaltijd->getMaaltijdId()}" title="Maaltijd wijzigen" class="knop rounded post modal">{icon get="pencil"}</a>
	{if $maaltijd->getMaaltijdRepetitieId()}
		<a href="/maaltijdenrepetities/beheer/{$maaltijd->getMaaltijdRepetitieId()}" title="Wijzig gekoppelde maaltijdrepetitie" class="knop rounded modal">{icon get="calendar_edit"}</a>
	{else}
		<div class="inline" style="width: 28px;"></div>
	{/if}
{/if}
		<a href="/corveebeheer/maaltijd/{$maaltijd->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop rounded">{icon get="chart_organisation"}</a>
	</td>
	<td>{$maaltijd->getDatum()|date_format:"%a %e %b"}</td>
	<td>{$maaltijd->getTitel()}</td>
	<td>
		<a href="{maalcieUrl}/fiscaal/{$maaltijd->getMaaltijdId()}" title="Toon fiscale maaltijdlijst&#013;Maaltijdprijs: &euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"}" class="knop rounded">{icon get="money_euro"}</a>
		<a href="/maaltijdenlijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst" class="knop rounded" style="margin-right:10px;">{icon get="table"}</a>
	</td>
	<td class="text-center">
		{$maaltijd->getAantalAanmeldingen()} ({$maaltijd->getAanmeldLimiet()})
	</td>
	<td>
{if !$maaltijd->getIsVerwijderd() and $maaltijd->getArchief() === null}
		<div class="float-right">
			<a href="{maalcieUrl}/anderaanmelden/{$maaltijd->getMaaltijdId()}" title="Aanmelding toevoegen" class="knop rounded post modal">{icon get="user_add"}</a>
			<a href="{maalcieUrl}/anderafmelden/{$maaltijd->getMaaltijdId()}" title="Aanmelding verwijderen" class="knop rounded post modal">{icon get="user_delete"}</a>
		</div>
{/if}
{if $maaltijd->getAanmeldFilter()}
		<div class="float-right">
			&nbsp;{icon get="group_key" title="Aanmeldfilter actief:&#013;"|cat:$maaltijd->getAanmeldFilter()}&nbsp;
		</div>
{/if}
	</td>
{if $maaltijd->getIsVerwijderd() or $maaltijd->getArchief() !== null}
	{if $maaltijd->getIsGesloten()}
	<td class="maaltijd-gesloten">
		Gesloten {icon get="lock" title="Laatst gesloten: "|cat:$maaltijd->getLaatstGesloten()}
	{else}
	<td class="maaltijd-open">Open
	{/if}
{else}
	{if $maaltijd->getIsGesloten()}
	<td class="maaltijd-gesloten">
		<a href="{maalcieUrl}/open/{$maaltijd->getMaaltijdId()}" title="Heropen deze maaltijd" class="knop post">Gesloten</a>
	{else}
	<td class="maaltijd-open">
		<a href="{maalcieUrl}/sluit/{$maaltijd->getMaaltijdId()}" title="Sluit deze maaltijd" class="knop post">Open</a>
	{/if}
{/if}
	</td>
	<td class="col-del">
{if $maaltijd->getIsVerwijderd()}
		<a href="{maalcieUrl}/verwijder/{$maaltijd->getMaaltijdId()}" title="Maaltijd definitief verwijderen" class="knop post confirm range"><input type=checkbox id="box-{$maaltijd->getMaaltijdId()}" name="del-maaltijd" />{icon get="cross"}</a>
{else}
		<a href="{maalcieUrl}/verwijder/{$maaltijd->getMaaltijdId()}" title="Maaltijd naar de prullenbak verplaatsen" class="knop post range"><input type=checkbox id="box-{$maaltijd->getMaaltijdId()}" name="del-maaltijd" />{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}