{*
	beheer_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="voorkeur-cell-{$voorkeur->getVanLidId()}-{$crid}"
	class="voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
	<a href="{Instellingen::get('taken', 'url')}/{if isset($uid)}uit{else}in{/if}schakelen/{$crid}/{$voorkeur->getVanLidId()}" class="knop post voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
		<input type="checkbox"
		  id="box-{$voorkeur->getVanLidId()}-{$crid}"
		  name="vrk-{$crid}"
		{if isset($uid)} checked="checked"{/if} />
	</a>
</td>
{/strip}