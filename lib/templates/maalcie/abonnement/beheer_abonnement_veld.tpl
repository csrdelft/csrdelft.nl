{*
	beheer_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="abonnement-cell-{$vanuid}-{$abonnement->getMaaltijdRepetitieId()}"
	class="abonnement-{if $abonnement->getFoutmelding()}error{elseif $abonnement->getWaarschuwing()}warning{else}{if $uid}in{else}uit{/if}geschakeld{/if}"
	title="{$abonnement->getFoutmelding()}{$abonnement->getWaarschuwing()}">
	<a href="{$smarty.const.maalcieUrl}/{if $uid}uit{else}in{/if}schakelen/{$abonnement->getMaaltijdRepetitieId()}/{$vanuid}" class="knop post abonnement-{if $uid}in{else}uit{/if}geschakeld">
		<input type="checkbox"
			   id="box-{$vanuid}-{$abonnement->getMaaltijdRepetitieId()}"
			   name="abo-{$abonnement->getMaaltijdRepetitieId()}"
		{if $uid} checked="checked"{/if} />
	</a>
</td>
{/strip}