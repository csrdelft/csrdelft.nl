{*
	mijn_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if is_null($uid)}
	<td id="voorkeur-row-{$crid}" class="voorkeur-uitgeschakeld">
		<a href="{$smarty.const.maalcieUrl}/inschakelen/{$crid}" class="btn post voorkeur-uitgeschakeld"><input type="checkbox" /> Nee</a>
	</td>
	{else}
	<td id="voorkeur-row-{$crid}" class="voorkeur-ingeschakeld">
		<a href="{$smarty.const.maalcieUrl}/uitschakelen/{$crid}" class="btn post voorkeur-ingeschakeld"><input type="checkbox" checked="checked" /> Ja</a>
	</td>
{/if}
