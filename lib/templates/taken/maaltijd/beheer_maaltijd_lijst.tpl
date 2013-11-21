{*
	beheer_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}"{if strtotime($maaltijd->getDatum()) < strtotime('-1 day')} class="taak-oud"{/if}>
	<td>
{if $maaltijd->getIsVerwijderd()}
		<a href="{$module}/herstel/{$maaltijd->getMaaltijdId()}" title="Maaltijd herstellen" class="knop post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$module}/bewerk/{$maaltijd->getMaaltijdId()}" title="Maaltijd wijzigen" class="knop post popup">{icon get="pencil"}</a>
	{if $maaltijd->getMaaltijdRepetitieId()}
		<a href="/actueel/taken/maaltijdrepetities/beheer/{$maaltijd->getMaaltijdRepetitieId()}" title="Beheer maaltijdrepetities" class="knop get">{icon get="calendar_edit"}</a>
	{/if}
{/if}
		<a href="/actueel/taken/corveebeheer/maaltijd/{$maaltijd->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop get">{icon get="chart_organisation"}</a>
	</td>
	<td>{$maaltijd->getDatum()|date_format:"%a %e %b"} {$maaltijd->getTijd()|date_format:"%H:%M"}</td>
	<td>{$maaltijd->getTitel()}
		
	</td>
	<td>
		<a href="{$module}/fiscaal/{$maaltijd->getMaaltijdId()}" title="Toon fiscale maaltijdlijst&#013;Maaltijdprijs: &euro; {$maaltijd->getPrijs()|string_format:"%.2f"}" class="knop">{icon get="money_euro"}</a>
		<a href="{$module}/lijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst" class="knop" style="margin-right:10px;">{icon get="table"}</a>
	</td>
	<td>
		{$maaltijd->getAantalAanmeldingen()} ({$maaltijd->getAanmeldLimiet()})
{if !$maaltijd->getIsVerwijderd()}
		<span style="float: right;">
			<a href="{$module}/anderaanmelden/{$maaltijd->getMaaltijdId()}" title="Aanmelding toevoegen" class="knop post popup">{icon get="user_add"}</a>
			<a href="{$module}/anderafmelden/{$maaltijd->getMaaltijdId()}" title="Aanmelding verwijderen" class="knop post popup">{icon get="user_delete"}</a>
		</span>
{/if}
{if $maaltijd->getAanmeldFilter()}
		<span style="float: right;">&nbsp;
			{icon get="group_key" title="Aanmeldfilter actief:&#013;"|cat:$maaltijd->getAanmeldFilter()}
			&nbsp;
		</span>
{/if}
	</td>
{if $maaltijd->getIsVerwijderd()}
	{if $maaltijd->getIsGesloten()}
	<td class="maaltijd-gesloten">Gesloten
		<span style="float: right;">&nbsp;
			{icon get="lock" title="Laatst gesloten: "|cat:$maaltijd->getLaatstGesloten()}
		</span>
	{else}
	<td class="maaltijd-open">Open
	{/if}
{else}
	{if $maaltijd->getIsGesloten()}
	<td class="maaltijd-gesloten">
		<a href="{$module}/open/{$maaltijd->getMaaltijdId()}" title="Heropen deze maaltijd" class="knop post">Gesloten</a>
	{else}
	<td class="maaltijd-open">
		<a href="{$module}/sluit/{$maaltijd->getMaaltijdId()}" title="Sluit deze maaltijd" class="knop post">Open</a>
	{/if}
{/if}
	</td>
	<td style="text-align:center;">
{if $maaltijd->getIsVerwijderd()}
		<a href="{$module}/verwijder/{$maaltijd->getMaaltijdId()}" title="Maaltijd definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
{else}
		<a href="{$module}/verwijder/{$maaltijd->getMaaltijdId()}" title="Maaltijd naar prullenbak verplaatsen" class="knop post">{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>