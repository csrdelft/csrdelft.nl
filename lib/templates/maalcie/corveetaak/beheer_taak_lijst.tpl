{*
	beheer_taak_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="corveetaak-row-{$taak->getTaakId()}" class="taak-datum-{$taak->getDatum()}
{if ($taak->getBeginMoment() < strtotime('-1 day') and !isset($maaltijd)) or $taak->getIsVerwijderd()} taak-oud{/if}
{if !isset($show) and !$prullenbak} verborgen{/if}">
	<td>
{if $taak->getIsVerwijderd()}
		<a href="{Instellingen::get('taken', 'url')}/herstel/{$taak->getTaakId()}" title="Corveetaak herstellen" class="knop rounded post">{icon get="arrow_undo"}</a>
{else}
		<a href="{Instellingen::get('taken', 'url')}/bewerk/{$taak->getTaakId()}" title="Taak wijzigen" class="knop rounded post modal">{icon get="pencil"}</a>
	{if $taak->getCorveeRepetitieId()}
		<a href="/corveerepetities/beheer/{$taak->getCorveeRepetitieId()}" title="Wijzig gekoppelde corveerepetitie" class="knop rounded modal">{icon get="calendar_edit"}</a>
	{else}
		<div class="inline" style="width: 28px;"></div>
	{/if}
{/if}
{if !isset($maaltijd) and $taak->getMaaltijdId()}
	<a href="/corveebeheer/maaltijd/{$taak->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="knop rounded">{icon get="cup_link"}</a>
{/if}
	</td>
	<td class="text-center" style="width: 50px;">
{assign var=aantal value=$taak->getAantalKeerGemaild()}
{if !$taak->getIsVerwijderd() and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
	{assign var="wijzigbaar" value="true"}
	{if $taak->getUid()}
		{$aantal}x
	{/if}
	<div class="float-right">
	{if $taak->getUid()}
		<a href="{Instellingen::get('taken', 'url')}/email/{$taak->getTaakId()}" title="Verstuur een (extra) herinnering voor deze taak" class="knop rounded post confirm">
	{/if}
{/if}
{if $taak->getIsTelaatGemaild()}
			{icon get="email_error" title="Laatste herinnering te laat verstuurd!&#013;"|cat:$taak->getWanneerGemaild()}
{elseif $aantal < 1}
	{if $taak->getUid()}
			{icon get="email" title="Niet gemaild"}
	{/if}
{elseif $aantal === 1}
			{icon get="email_go" title=$taak->getWanneerGemaild()}
{elseif $aantal > 1}
			{icon get="email_open" title=$taak->getWanneerGemaild()}
{/if}
{if isset($wijzigbaar)}
	{if $taak->getUid()}
		</a>
	{/if}
	</div>
{/if}
	</td>
	<td>{$taak->getDatum()|date_format:"%a %e %b"}</td>
	<td style="width: 100px;">{$taak->getCorveeFunctie()->naam}</td>
	<td class="niet-dik taak-{if $taak->getUid()}toegewezen{elseif  $taak->getBeginMoment() < strtotime(Instellingen::get('corvee', 'waarschuwing_taaktoewijzing_vooraf'))}warning{else}open{/if}">
{if isset($wijzigbaar)}
		<a href="{Instellingen::get('taken', 'url')}/toewijzen/{$taak->getTaakId()}" title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="knop rounded post modal dragobject static ruilen" id="taak-{$taak->getTaakId()}"{if $taak->getUid()} uid="{$taak->getUid()}">{icon get="user_green"}{else}>{icon get="user_red"}{/if}</a>
{/if}
{if $taak->getUid()}
		&nbsp;{Lid::naamLink($taak->getUid(), Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}
{/if}
	</td>
	<td{if $taak->getUid() and ($taak->getPunten() !== $taak->getPuntenToegekend() or $taak->getBonusMalus() !== $taak->getBonusToegekend()) and $taak->getBeginMoment() < strtotime(Instellingen::get('corvee', 'waarschuwing_puntentoewijzing_achteraf'))} class="taak-warning"{/if}>
		{$taak->getPuntenToegekend()}
{if $taak->getBonusToegekend() > 0}
	+
{/if}
{if $taak->getBonusToegekend() !== 0}
	{$taak->getBonusToegekend()}
{/if}
&nbsp;van {$taak->getPunten()}
{if $taak->getBonusMalus() > 0}
	+
{/if}
{if $taak->getBonusMalus() !== 0}
	{$taak->getBonusMalus()}
{/if}
{if isset($wijzigbaar) and $taak->getUid()}
		<div class="float-right">
	{if $taak->getWanneerToegekend()}
		<a href="{Instellingen::get('taken', 'url')}/puntenintrekken/{$taak->getTaakId()}" title="Punten intrekken" class="knop rounded post">{icon get="medal_silver_delete"}</a>
	{else}
		<a href="{Instellingen::get('taken', 'url')}/puntentoekennen/{$taak->getTaakId()}" title="Punten toekennen" class="knop rounded post">{icon get="award_star_add"}</a>
	{/if}
{/if}
		</div>
	</td>
	<td class="col-del">
{if $taak->getIsVerwijderd()}
		<a href="{Instellingen::get('taken', 'url')}/verwijder/{$taak->getTaakId()}" title="Corveetaak definitief verwijderen" class="knop post confirm range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del-taak" />{icon get="cross"}</a>
{else}
		<a href="{Instellingen::get('taken', 'url')}/verwijder/{$taak->getTaakId()}" title="Corveetaak naar de prullenbak verplaatsen" class="knop post range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del-taak" />{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}
