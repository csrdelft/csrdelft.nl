{*
	beheer_abonnementen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u alle abonnementen beheren en zoeken.
</p>
<form method="post" action="{$module}/novieten" class="Formulier popup" style="float: right;">
	Abonneer novieten op:
	<select name="mrid" onchange="taken_submit_dropdown($(this).parent());">
		<option selected="selected">kies</option>
{foreach from=$aborepetities item=repetitie}
		<option value="{$repetitie->getMaaltijdRepetitieId()}" class="save">{$repetitie->getStandaardTitel()}</option>
{/foreach}
	</select>
</form>
<div style="width: 30%; display: inline-block;"><label for="toon">Toon abonnementen:</label>
</div><select name="toon" onchange="taken_loading();location.href='{$module}/'+this.value;" style="margin: 3px 1px;">
	<option value="beheer" class="arrow"{if $toon === 'waarschuwing'} selected="selected"{/if}>waarschuwingen</option>
	<option value="ingeschakeld" class="arrow"{if $toon === 'in'} selected="selected"{/if}>ingeschakeld</option>
	<option value="abonneerbaar" class="arrow"{if $toon === 'abo'} selected="selected"{/if}>abonneerbaar</option>
</select>
{$form->view()}
<table id="taken-tabel" class="taken-tabel">
{foreach name=tabel from=$matrix key=uid item=abonnementen}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
		{include file='taken/abonnement/beheer_abonnement_head.tpl' repetities=$repetities}
	<tbody>
	{/if}
	{include file='taken/abonnement/beheer_abonnement_lijst.tpl' uid=$uid abonnementen=$abonnementen}
{/foreach}
{if !$matrix}
	{include file='taken/abonnement/beheer_abonnement_head.tpl' repetities=$repetities}
	<tbody>
{/if}
	</tbody>
</table>