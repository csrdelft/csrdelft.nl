{*
	beheer_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->maaltijd_id}"
{if strtotime($maaltijd->datum) < strtotime('-1 day')} class="
	{if !$prullenbak} taak-maaltijd-oud{/if} taak-oud"
{/if}>
	<td>
{if $maaltijd->archief !== null}
		<div class="inline" style="width: 28px;">{icon get="compress" title="Maaltijd is gearchiveerd"}</div>
{elseif $maaltijd->verwijderd}
		<a href="{$smarty.const.maalcieUrl}/herstel/{$maaltijd->maaltijd_id}" title="Maaltijd herstellen" class="btn post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$maaltijd->maaltijd_id}" title="Maaltijd wijzigen" class="btn post popup">{icon get="pencil"}</a>
	{if $maaltijd->mlt_repetitie_id}
		<a href="/maaltijdenrepetities/beheer/{$maaltijd->mlt_repetitie_id}" title="Wijzig gekoppelde maaltijdrepetitie" class="btn popup">{icon get="calendar_edit"}</a>
	{else}
		<div class="inline" style="width: 28px;"></div>
	{/if}
{/if}
		<a href="/corveebeheer/maaltijd/{$maaltijd->maaltijd_id}" title="Beheer maaltijdcorvee" class="btn">{icon get="chart_organisation"}</a>
	</td>
	<td>{$maaltijd->datum|date_format:"%a %e %b"}</td>
	<td>{$maaltijd->titel}</td>
	<td>
		<a href="{$smarty.const.maalcieUrl}/fiscaal/{$maaltijd->maaltijd_id}" title="Toon fiscale maaltijdlijst&#013;Maaltijdprijs: &euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"}" class="btn">{icon get="money_euro"}</a>
		<a href="/maaltijdenlijst/{$maaltijd->maaltijd_id}" title="Toon maaltijdlijst" class="btn" style="margin-right:10px;">{icon get="table"}</a>
	</td>
	<td class="text-center">
		{$maaltijd->aantal_aanmeldingen} ({$maaltijd->aanmeld_limiet})

		{if !$maaltijd->verwijderd and $maaltijd->archief === null}
			<div style="float: right;">
				<a href="{$smarty.const.maalcieUrl}/anderaanmelden/{$maaltijd->maaltijd_id}" title="Aanmelding toevoegen" class="btn post popup">{icon get="user_add"}</a>
				<a href="{$smarty.const.maalcieUrl}/anderafmelden/{$maaltijd->maaltijd_id}" title="Aanmelding verwijderen" class="btn post popup">{icon get="user_delete"}</a>
			</div>
		{/if}
	</td>
	<td>
{if $maaltijd->aanmeld_filter}
			&nbsp;{icon get="group_key" title="Aanmeldfilter actief:&#013;"|cat:$maaltijd->aanmeld_filter}&nbsp;
{/if}
	</td>
{if $maaltijd->verwijderd or $maaltijd->archief !== null}
	{if $maaltijd->gesloten}
	<td class="maaltijd-gesloten">
		Gesloten {icon get="lock" title="Laatst gesloten: "|cat:$maaltijd->laatst_gesloten}
	{else}
	<td class="maaltijd-open">Open
	{/if}
{else}
	{if $maaltijd->gesloten}
	<td class="maaltijd-gesloten">
		<a href="{$smarty.const.maalcieUrl}/open/{$maaltijd->maaltijd_id}" title="Heropen deze maaltijd" class="btn post">Gesloten</a>
	{else}
	<td class="maaltijd-open">
		<a href="{$smarty.const.maalcieUrl}/sluit/{$maaltijd->maaltijd_id}" title="Sluit deze maaltijd" class="btn post">Open</a>
	{/if}
{/if}
	</td>
	<td class="col-del">
{if $maaltijd->verwijderd}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$maaltijd->maaltijd_id}" title="Maaltijd definitief verwijderen" class="btn post confirm">{icon get="cross"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$maaltijd->maaltijd_id}" title="Maaltijd naar de prullenbak verplaatsen" class="btn post">{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}