{*
	mijn_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="abonnement-cell-{$mrid}" {if isset($uid)}class="abonnement-ingeschakeld">
	<a href="{$smarty.const.maalcieUrl}/uitschakelen/{$mrid}" class="btn post abonnement-ingeschakeld"><input type="checkbox" checked="checked" /> Aan</a>
{else}class="abonnement-uitgeschakeld">
	<a href="{$smarty.const.maalcieUrl}/inschakelen/{$mrid}" class="btn post abonnement-uitgeschakeld"><input type="checkbox" /> Uit</a>	
{/if}
</td>