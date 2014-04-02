{*
	beheer_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="abonnement-cell-{$uid}-{$abonnement->getMaaltijdRepetitieId()}"
	class="abonnement-{if $abonnement->getWaarschuwing()}warning{else}{if $lidid}in{else}uit{/if}geschakeld{/if}"
	title="{$abonnement->getWaarschuwing()}">
	<a href="{Instellingen::get('taken', 'url')}/{if $lidid}uit{else}in{/if}schakelen/{$abonnement->getMaaltijdRepetitieId()}"
	   postdata="voor_lid={$uid}"
	   class="knop post abonnement-{if $lidid}in{else}uit{/if}geschakeld">
		
		<input type="checkbox"
			   id="box-{$uid}-{$abonnement->getMaaltijdRepetitieId()}"
			   name="abo-{$abonnement->getMaaltijdRepetitieId()}"
		{if $lidid} checked="checked"{/if} />
	</a>
</td>
{/strip}