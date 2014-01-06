{*
	beheer_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="voorkeur-cell-{$voorkeur->getLid()->getUid()}-{$crid}"
	class="voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
	<a href="{$globals.taken_module}/{if isset($uid)}uit{else}in{/if}schakelen/{$crid}"
	   post="voor_lid={$voorkeur->getLid()->getUid()}"
	   class="knop post voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
		
		<input type="checkbox"
		  id="box-{$voorkeur->getLid()->getUid()}-{$crid}"
		  name="vrk-{$crid}"
		{if isset($uid)} checked="checked"{/if} />
	</a>
</td>
{/strip}