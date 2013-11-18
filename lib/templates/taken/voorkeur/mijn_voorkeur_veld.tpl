{*
	mijn_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="voorkeur-row-{$crid}" {if isset($uid)}class="voorkeur-ingeschakeld">
	<a href="{$module}/uitschakelen/{$crid}" class="knop post voorkeur-ingeschakeld"><input type="checkbox" checked="checked" /> Aan</a>
{else}class="voorkeur-uitgeschakeld">
	<a href="{$module}/inschakelen/{$crid}" class="knop post voorkeur-uitgeschakeld"><input type="checkbox" /> Uit</a>	
{/if}
</td>