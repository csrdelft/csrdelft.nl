{*
	beheer_taak_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveetaak-row-{$taak->getTaakId()}"{if $taak->getBeginMoment() < strtotime('-1 day')} class="taak-oud"{/if}>
	<td>
{if $taak->getIsVerwijderd()}
		<a href="{$module}/herstel/{$taak->getTaakId()}" title="Corveetaak herstellen" class="knop post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$module}/bewerk/{$taak->getTaakId()}" title="Taak wijzigen" class="knop post popup">{icon get="pencil"}</a>
	{if $taak->getCorveeRepetitieId()}
		<a href="/actueel/taken/corveerepetities/beheer/{$taak->getCorveeRepetitieId()}" title="Wijzig gekoppelde corveerepetitie" class="knop get">{icon get="calendar_edit"}</a>
	{/if}
{/if}
{if $taak->getMaaltijdId() and !isset($maaltijd)}
	<a href="{$module}/maaltijd/{$taak->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop get">{icon get="cup_link"}</a>
{/if}
	</td>
	<td style="text-align: center;">{assign var=aantal value=$taak->getAantalKeerGemaild()}{$aantal}x
		<div style="float: right;">
{if !$taak->getIsVerwijderd() and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
	{assign var="wijzigbaar" value="true"}
	{if $taak->getLidId()}
			<a href="{$module}/email/{$taak->getTaakId()}" title="Verstuur een (extra) herinnering voor deze taak" class="knop post confirm">
	{/if}
{/if}
{if $taak->getIsTelaatGemaild()}
			{icon get="email_error" title="Niet op tijd gemaild!&#013;"|cat:$taak->getWanneerGemaild()}
{elseif $aantal < 1}
			{icon get="email" title="Niet gemaild"}
{elseif $aantal === 1}
			{icon get="email_go" title=$taak->getWanneerGemaild()}
{elseif $aantal > 1}
			{icon get="email_open" title=$taak->getWanneerGemaild()}
{/if}
{if isset($wijzigbaar)}
			</a>
{/if}
		</div>
	</td>
	<td>{$taak->getDatum()|date_format:"%a %e/%m/%y"}</td>
	<td>{$taak->getCorveeFunctie()->getNaam()}</td>
	<td class="taak-{if $taak->getLidId()}toegewezen{elseif  strtotime($taak->getDatum()) < strtotime($vooraf)}warning{else}open{/if}" style="font-weight: normal;">
{if isset($wijzigbaar)}
		<a href="{$module}/toewijzen/{$taak->getTaakId()}" title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="knop post popup ruilen" id="taak-{$taak->getTaakId()}"{if $taak->getLidId()} lid_id="{$taak->getLidId()}">{icon get="user_green"}{else}>{icon get="user_red"}{/if}</a>
{/if}
{if $taak->getLidId()}
		&nbsp;{$taak->getLid()->getNaamLink($ledenweergave, 'link')}
{/if}
	</td>
	<td{if $taak->getLidId() and ($taak->getPunten() !== $taak->getPuntenToegekend() or $taak->getBonusMalus() !== $taak->getBonusToegekend()) and strtotime($taak->getDatum()) < strtotime($achteraf)} class="taak-warning"{/if}>
		{$taak->getPuntenToegekend()}{if $taak->getBonusToegekend() > 0}+{/if}{if $taak->getBonusToegekend() !== 0}{$taak->getBonusToegekend()}{/if}
		van
		{$taak->getPunten()}{if $taak->getBonusMalus() > 0}+{/if}{if $taak->getBonusMalus() !== 0}{$taak->getBonusMalus()}{/if}
	{if isset($wijzigbaar) and $taak->getLidId()}
		<div style="float: right;">
		<a href="{$module}/puntentoekennen/{$taak->getTaakId()}" title="Punten toekennen" class="knop post">{icon get="award_star_add"}</a>
		<a href="{$module}/puntenintrekken/{$taak->getTaakId()}" title="Punten intrekken" class="knop post">{icon get="medal_silver_delete"}</a>
	{/if}
		</div>
	</td>
	<td class="col-del">
{if $taak->getIsVerwijderd()}
		<a href="{$module}/verwijder/{$taak->getTaakId()}" title="Corveetaak definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
{else}
		<a href="{$module}/verwijder/{$taak->getTaakId()}" title="Corveetaak naar prullenbak verplaatsen" class="knop post">{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>