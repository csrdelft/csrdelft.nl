{*
	beheer_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="abonnement-cell-{$uid}-{$abonnement->getMaaltijdRepetitieId()}" class="abonnement-{if $abonnement->getWaarschuwing()}warning{else}{if $lidid}in{else}uit{/if}geschakeld{/if}" title="{$abonnement->getWaarschuwing()}" style="vertical-align: middle;">
	<a href="{$module}/{if $lidid}uit{else}in{/if}schakelen/{$abonnement->getMaaltijdRepetitieId()}" post="voor_lid={$uid}" class="knop post abonnement-{if $lidid}in{else}uit{/if}geschakeld"><input type="checkbox" {if $lidid}checked="checked"{/if} /></a>
</td>