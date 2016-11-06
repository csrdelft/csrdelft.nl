{*
	beheer_maaltijden.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if $prullenbak}
<p>
Op deze pagina kunt u de maaltijden herstellen of definitief verwijderen.
Onderstaande tabel toont alle maaltijden die in de prullenbak zitten.
</p>
<p>
N.B. Voor het definitief verwijderen van een maaltijd moeten eerst de gekoppelde corveetaken definitief zijn verwijderd.
</p>
{elseif $archief}
<p>
Onderstaande tabel toont alle maaltijden die in het archief zitten.
</p>
<p>
N.B. Het archief is alleen-lezen.
</p>
{else}
<p>
Op deze pagina kunt u de maaltijden aanmaken, wijzigen en verwijderen.
Onderstaande tabel toont alle maaltijden die niet verwijderd zijn.
</p>
<br />
<div class="float-right">
	<a class="btn" onclick="$(this).hide();$('tr.taak-maaltijd-oud').show();">{icon get="eye"} Toon verleden</a>
	<a href="{$smarty.const.maalcieUrl}/prullenbak" class="btn">{icon get="bin_closed"} Open prullenbak</a>
	<a href="{$smarty.const.maalcieUrl}/nieuw" class="btn post popup">{icon get="add"} Nieuwe maaltijd</a>
</div>
<form action="{$smarty.const.maalcieUrl}/nieuw" method="post" class="Formulier ModalForm SubmitReset">
	<label for="mrid" style="width: auto;">{icon get="calendar_add"} Periodieke maaltijden aanmaken:</label>&nbsp;
	<select id="mrid" name="mlt_repetitie_id" origvalue="kies" class="FormElement SubmitChange">
		<option selected="selected">kies</option>
	{foreach from=$repetities item=repetitie}
		<option value="{$repetitie->mlt_repetitie_id}">{$repetitie->standaard_titel}</option>
	{/foreach}
	</select>
	<a href="/maaltijdenrepetities" class="btn" title="Periodieke maaltijden beheren">{icon get="calendar_edit"}</a>
</form>
{/if}
<br />
<table id="maalcie-tabel" class="maalcie-tabel">
{foreach name=tabel from=$maaltijden item=maaltijd}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
		{include file='maalcie/maaltijd/beheer_maaltijd_head.tpl'}
	<tbody>
	{/if}
	{if $archief}
		{include file='maalcie/maaltijd/beheer_maaltijd_archief.tpl'}
	{else}
		{include file='maalcie/maaltijd/beheer_maaltijd_lijst.tpl'}
	{/if}
{/foreach}
{if !$maaltijden}
	{include file='maalcie/maaltijd/beheer_maaltijd_head.tpl'}
	<tbody>
{/if}
	</tbody>
</table>