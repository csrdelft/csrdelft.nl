{*
	beheer_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="voorkeur-cell-{$voorkeur->getLid()->getUid()}-{$crid}" class="voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld" style="vertical-align: middle;">
	<a href="{$module}/{if isset($uid)}uit{else}in{/if}schakelen/{$crid}" post="voor_lid={$voorkeur->getLid()->getUid()}" class="knop post voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld"><input type="checkbox" {if isset($uid)}checked="checked"{/if} /></a>
</td>