{*
	beheer_abonnementen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u alle abonnementen beheren en zoeken.
</p>
<form action="/maaltijden/abonnementen/beheer/novieten" method="post" class="Formulier ModalForm SubmitReset float-right">
	{printCsrfField()}
	Abonneer novieten op:
	<select name="mrid" origvalue="kies" class="FormElement SubmitChange">
		<option selected="selected">kies</option>
{foreach from=$aborepetities item=repetitie}
		<option value="{$repetitie->mlt_repetitie_id}" class="save">{$repetitie->standaard_titel}</option>
{/foreach}
	</select>
</form>
<div class="inline" style="width: 30%;"><label for="toon">Toon abonnementen:</label>
</div><select name="toon" onchange="location.href='/maaltijden/abonnementen/beheer/'+this.value;">
	<option value="waarschuwingen" class="arrow"{if $toon === 'waarschuwing'} selected="selected"{/if}>waarschuwingen</option>
	<option value="ingeschakeld" class="arrow"{if $toon === 'in'} selected="selected"{/if}>ingeschakeld</option>
	<option value="abonneerbaar" class="arrow"{if $toon === 'abo'} selected="selected"{/if}>abonneerbaar</option>
</select>
<p>&nbsp;</p>
<table id="maalcie-tabel" class="maalcie-tabel">
{foreach name=tabel from=$matrix key=vanuid item=abonnementen}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
		{include file='maalcie/abonnement/beheer_abonnement_head.tpl'}
	<tbody>
	{/if}
	{include file='maalcie/abonnement/beheer_abonnement_lijst.tpl'}
{/foreach}
{if !$matrix}
	{include file='maalcie/abonnement/beheer_abonnement_head.tpl'}
	<tbody>
{/if}
	</tbody>
</table>
