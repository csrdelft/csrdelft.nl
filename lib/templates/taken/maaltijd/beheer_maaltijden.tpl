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
	{if $maaltijden}
<div style="float: right;"><a href="{$globals.taken_module}/leegmaken" title="Alle maaltijden in de prullenbak definitief verwijderen" class="knop get confirm">{icon get="bin"} Prullenbak leegmaken</a></div>
	{/if}
{else}
<p>
Op deze pagina kunt u de maaltijden aanmaken, wijzigen en verwijderen.
Onderstaande tabel toont alle maaltijden die niet verwijderd zijn.
</p>
<div style="float: right;"><a href="{$globals.taken_module}/nieuw" title="Nieuwe maaltijd" class="knop post popup">{icon get="add"} Nieuwe maaltijd</a></div>
<form method="post" action="{$globals.taken_module}/nieuw" class="Formulier popup">
	<label for="mrid">{icon get="calendar_add"} Periodieke maaltijden aanmaken:</label>
	<select name="mrid" onchange="taken_submit_dropdown($(this).parent());">
		<option selected="selected">kies</option>
	{foreach from=$repetities item=repetitie}
		<option value="{$repetitie->getMaaltijdRepetitieId()}">{$repetitie->getStandaardTitel()}</option>
	{/foreach}
	</select>
</form>
{/if}
<table id="taken-tabel" class="taken-tabel">
{foreach name=tabel from=$maaltijden item=maaltijd}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
		{include file='taken/maaltijd/beheer_maaltijd_head.tpl' prullenbak=$prullenbak}
	<tbody>
	{/if}
	{include file='taken/maaltijd/beheer_maaltijd_lijst.tpl' maaltijd=$maaltijd}
{/foreach}
{if !$maaltijden}
	{include file='taken/maaltijd/beheer_maaltijd_head.tpl' prullenbak=$prullenbak}
	<tbody>
{/if}
	</tbody>
</table>