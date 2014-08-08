{*
	beheer_abonnementen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u alle abonnementen beheren en zoeken.
</p>
<form action="{Instellingen::get('taken', 'url')}/novieten" method="post" class="Formulier popup SubmitReset" style="float: right;">
	Abonneer novieten op:
	<select name="mrid" origvalue="kies" class="FormField SubmitChange">
		<option selected="selected">kies</option>
{foreach from=$aborepetities item=repetitie}
		<option value="{$repetitie->getMaaltijdRepetitieId()}" class="save">{$repetitie->getStandaardTitel()}</option>
{/foreach}
	</select>
</form>
<div style="width: 30%; display: inline-block;"><label for="toon">Toon abonnementen:</label>
</div><select name="toon" onchange="location.href='{Instellingen::get('taken', 'url')}/'+this.value;">
	<option value="waarschuwingen" class="arrow"{if $toon === 'waarschuwing'} selected="selected"{/if}>waarschuwingen</option>
	<option value="ingeschakeld" class="arrow"{if $toon === 'in'} selected="selected"{/if}>ingeschakeld</option>
	<option value="abonneerbaar" class="arrow"{if $toon === 'abo'} selected="selected"{/if}>abonneerbaar</option>
</select>
{$form->view()}
<table id="maalcie-tabel" class="maalcie-tabel">
{foreach name=tabel from=$matrix key=uid item=abonnementen}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
		{include file='maalcie/abonnement/beheer_abonnement_head.tpl' repetities=$repetities}
	<tbody>
	{/if}
	{include file='maalcie/abonnement/beheer_abonnement_lijst.tpl' uid=$uid abonnementen=$abonnementen}
{/foreach}
{if !$matrix}
	{include file='maalcie/abonnement/beheer_abonnement_head.tpl' repetities=$repetities}
	<tbody>
{/if}
	</tbody>
</table>