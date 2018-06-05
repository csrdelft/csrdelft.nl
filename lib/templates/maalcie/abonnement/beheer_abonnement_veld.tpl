{*
	beheer_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="abonnement-cell-{$vanuid}-{$abonnement->mlt_repetitie_id}"
	class="abonnement-{if $abonnement->foutmelding}error{elseif $abonnement->waarschuwing}warning{else}{if $uid}in{else}uit{/if}geschakeld{/if}"
	title="{$abonnement->foutmelding}{$abonnement->waarschuwing}">
	<a href="{$smarty.const.maalcieUrl}/{if $uid}uit{else}in{/if}schakelen/{$abonnement->mlt_repetitie_id}/{$vanuid}" class="btn post abonnement-{if $uid}in{else}uit{/if}geschakeld">
		<input type="checkbox"
			   id="box-{$vanuid}-{$abonnement->mlt_repetitie_id}"
			   name="abo-{$abonnement->mlt_repetitie_id}"
		{if $uid} checked="checked"{/if} />
	</a>
</td>
{/strip}