{*
	beheer_taak_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="corveetaak-row-{$taak->taak_id}" class="taak-datum-{$taak->datum}
{if ($taak->getBeginMoment() < strtotime('-1 day') and !isset($maaltijd)) or $taak->verwijderd} taak-oud{/if}
{if !isset($show) and !$prullenbak} verborgen{/if}">
	<td>
{if $taak->verwijderd}
		<a href="{$smarty.const.maalcieUrl}/herstel/{$taak->taak_id}" title="Corveetaak herstellen" class="btn post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$taak->taak_id}" title="Taak wijzigen" class="btn post popup">{icon get="pencil"}</a>
	{if $taak->crv_repetitie_id}
		<a href="/corveerepetities/beheer/{$taak->crv_repetitie_id}" title="Wijzig gekoppelde corveerepetitie" class="btn popup">{icon get="calendar_edit"}</a>
	{else}
		<div class="inline" style="width: 28px;"></div>
	{/if}
{/if}
{if !isset($maaltijd) and $taak->maaltijd_id}
	<a href="/corveebeheer/maaltijd/{$taak->maaltijd_id}" title="Beheer maaltijdcorvee" class="btn">{icon get="cup_link"}</a>
{/if}
	</td>
	<td class="text-center" style="width: 50px;">
{assign var=aantal value=$taak->getAantalKeerGemaild()}
{if !$taak->verwijderd and (!isset($maaltijd) or !$maaltijd->verwijderd)}
	{assign var="wijzigbaar" value="true"}
	{if $taak->uid}
		{$aantal}x
	{/if}
	<div class="float-right">
	{if $taak->uid}
		<a href="{$smarty.const.maalcieUrl}/email/{$taak->taak_id}" title="Verstuur een (extra) herinnering voor deze taak" class="btn post confirm">
	{/if}
{/if}
{if $taak->getIsTelaatGemaild()}
			{icon get="email_error" title="Laatste herinnering te laat verstuurd!&#013;"|cat:$taak->wanneer_gemaild}
{elseif $aantal < 1}
	{if $taak->uid}
			{icon get="email" title="Niet gemaild"}
	{/if}
{elseif $aantal === 1}
			{icon get="email_go" title=$taak->wanneer_gemaild}
{elseif $aantal > 1}
			{icon get="email_open" title=$taak->wanneer_gemaild}
{/if}
{if isset($wijzigbaar)}
	{if $taak->uid}
		</a>
	{/if}
	</div>
{/if}
	</td>
	<td>{$taak->datum|date_format:"%a %e %b"}</td>
	<td style="width: 100px;">{$taak->getCorveeFunctie()->naam}</td>
	<td class="niet-dik taak-{if $taak->uid}toegewezen{elseif  $taak->getBeginMoment() < strtotime(CsrDelft\model\InstellingenModel::get('corvee', 'waarschuwing_taaktoewijzing_vooraf'))}warning{else}open{/if}">
{if isset($wijzigbaar)}
		<a href="{$smarty.const.maalcieUrl}/toewijzen/{$taak->taak_id}" id="taak-{$taak->taak_id}" title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="btn post popup dragobject ruilen" style="position: static;"{if $taak->uid} uid="{$taak->uid}">{icon get="user_green"}{else}>{icon get="user_red"}{/if}</a>
{/if}
{if $taak->uid}
		&nbsp;{CsrDelft\model\ProfielModel::getLink($taak->uid,CsrDelft\model\InstellingenModel::get('corvee', 'weergave_ledennamen_beheer'))}
{/if}
	</td>
	<td{if $taak->uid and ($taak->punten !== $taak->punten_toegekend or $taak->bonus_malus !== $taak->bonus_toegekend) and $taak->getBeginMoment() < strtotime(CsrDelft\model\InstellingenModel::get('corvee', 'waarschuwing_puntentoewijzing_achteraf'))} class="taak-warning"{/if}>
		{$taak->punten_toegekend}
{if $taak->bonus_toegekend > 0}
	+
{/if}
{if $taak->bonus_toegekend !== 0}
	{$taak->bonus_toegekend}
{/if}
&nbsp;van {$taak->punten}
{if $taak->bonus_malus > 0}
	+
{/if}
{if $taak->bonus_malus !== 0}
	{$taak->bonus_malus}
{/if}
{if isset($wijzigbaar) and $taak->uid}
		<div class="float-right">
	{if $taak->wanneer_toegekend}
		<a href="{$smarty.const.maalcieUrl}/puntenintrekken/{$taak->taak_id}" title="Punten intrekken" class="btn post">{icon get="medal_silver_delete"}</a>
	{else}
		<a href="{$smarty.const.maalcieUrl}/puntentoekennen/{$taak->taak_id}" title="Punten toekennen" class="btn post">{icon get="award_star_add"}</a>
	{/if}
{/if}
		</div>
	</td>
	<td class="col-del">
{if $taak->verwijderd}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$taak->taak_id}" title="Corveetaak definitief verwijderen" class="btn post confirm range"><input type=checkbox id="box-{$taak->taak_id}" name="del-taak" />{icon get="cross"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$taak->taak_id}" title="Corveetaak naar de prullenbak verplaatsen" class="btn post range"><input type=checkbox id="box-{$taak->taak_id}" name="del-taak" />{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}
