{*
	beheer_taak_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="corveetaak-row-{$taak->getTaakId()}" class="taak-datum-{$taak->getDatum()}
{if $taak->getBeginMoment() < strtotime('-1 day')} taak-oud{/if}
"{if !isset($show) and !$prullenbak} style="display: none;"{/if}>
	<td>
{if $taak->getIsVerwijderd()}
		<a href="{$globals.taken_module}/herstel/{$taak->getTaakId()}" title="Corveetaak herstellen" class="knop post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$globals.taken_module}/bewerk/{$taak->getTaakId()}" title="Taak wijzigen" class="knop post popup">{icon get="pencil"}</a>
	{if $taak->getCorveeRepetitieId()}
		<a href="/corveerepetities/beheer/{$taak->getCorveeRepetitieId()}" title="Wijzig gekoppelde corveerepetitie" class="knop get popup">{icon get="calendar_edit"}</a>
	{/if}
{/if}
	</td>
	<td style="width: 50px; text-align: center;">
{assign var=aantal value=$taak->getAantalKeerGemaild()}
{if !$taak->getIsVerwijderd() and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
	{assign var="wijzigbaar" value="true"}
	{if $taak->getLidId()}
		{$aantal}x
	{/if}
	<div style="float: right;">
	{if $taak->getLidId()}
		<a href="{$globals.taken_module}/email/{$taak->getTaakId()}" title="Verstuur een (extra) herinnering voor deze taak" class="knop post confirm">
	{/if}
{/if}
{if $taak->getIsTelaatGemaild()}
			{icon get="email_error" title="Laatste herinnering te laat verstuurd!&#013;"|cat:$taak->getWanneerGemaild()}
{elseif $aantal < 1}
	{if $taak->getLidId()}
			{icon get="email" title="Niet gemaild"}
	{/if}
{elseif $aantal === 1}
			{icon get="email_go" title=$taak->getWanneerGemaild()}
{elseif $aantal > 1}
			{icon get="email_open" title=$taak->getWanneerGemaild()}
{/if}
{if isset($wijzigbaar)}
	{if $taak->getLidId()}
		</a>
	{/if}
	</div>
{/if}
	</td>
	<td>{$taak->getDatum()|date_format:"%a %e %b"}</td>
	<td style="width: 100px;">{$taak->getCorveeFunctie()->getNaam()}</td>
	<td class="taak-{if $taak->getLidId()}toegewezen{elseif  $taak->getBeginMoment() < strtotime($globals.waarschuwing_taaktoewijzing_vooraf)}warning{else}open{/if}" style="font-weight: normal;">
{if isset($wijzigbaar)}
		<a href="{$globals.taken_module}/toewijzen/{$taak->getTaakId()}" title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="knop post popup dragobject ruilen" id="taak-{$taak->getTaakId()}"{if $taak->getLidId()} lid_id="{$taak->getLidId()}">{icon get="user_green"}{else}>{icon get="user_red"}{/if}</a>
{/if}
{if $taak->getLidId()}
		&nbsp;{$taak->getLid()->getNaamLink($globals.weergave_ledennamen_beheer, 'link')}
{/if}
	</td>
	<td{if $taak->getLidId() and ($taak->getPunten() !== $taak->getPuntenToegekend() or $taak->getBonusMalus() !== $taak->getBonusToegekend()) and $taak->getBeginMoment() < strtotime($globals.waarschuwing_puntentoewijzing_achteraf)} class="taak-warning"{/if}>
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
{if isset($wijzigbaar) and $taak->getLidId()}
		<div style="float: right;">
	{if $taak->getWanneerToegekend()}
		<a href="{$globals.taken_module}/puntenintrekken/{$taak->getTaakId()}" title="Punten intrekken" class="knop post">{icon get="medal_silver_delete"}</a>
	{else}
		<a href="{$globals.taken_module}/puntentoekennen/{$taak->getTaakId()}" title="Punten toekennen" class="knop post">{icon get="award_star_add"}</a>
	{/if}
{/if}
		</div>
	</td>
	<td class="col-del">
{if $taak->getIsVerwijderd()}
		<a href="{$globals.taken_module}/verwijder/{$taak->getTaakId()}" title="Corveetaak definitief verwijderen" class="knop post confirm range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del" /> {icon get="cross"}</a>
{else}
		<a href="{$globals.taken_module}/verwijder/{$taak->getTaakId()}" title="Corveetaak naar prullenbak verplaatsen" class="knop post range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del" /> {icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}