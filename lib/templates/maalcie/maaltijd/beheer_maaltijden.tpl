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
<div class="float-right">
	<a class="knop" onclick="$(this).hide();$('tr.taak-maaltijd-oud').show();">{icon get="eye"} Toon verleden</a>
	<a href="{$smarty.const.maalcieUrl}/nieuw" title="Nieuwe maaltijd" class="knop post modal">{icon get="add"} Nieuwe maaltijd</a>
</div>
<form action="{$smarty.const.maalcieUrl}/nieuw" method="post" class="Formulier modal SubmitReset">
	<label for="mrid" style="width: auto;">{icon get="calendar_add"} Periodieke maaltijden aanmaken:</label>&nbsp;
	<select name="mrid" origvalue="kies" class="FormElement SubmitChange">
		<option selected="selected">kies</option>
	{foreach from=$repetities item=repetitie}
		<option value="{$repetitie->getMaaltijdRepetitieId()}">{$repetitie->getStandaardTitel()}</option>
	{/foreach}
	</select>
</form>
{/if}
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