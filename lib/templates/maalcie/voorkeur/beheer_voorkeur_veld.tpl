{*
	beheer_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<td id="voorkeur-cell-{$voorkeur->getVanUid()}-{$crid}"
	class="voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
	<a href="/corvee/voorkeuren/beheer/{if isset($uid)}uit{else}in{/if}schakelen/{$crid}/{$voorkeur->getVanUid()}" class="btn post voorkeur-{if isset($uid)}in{else}uit{/if}geschakeld">
		<input type="checkbox"
		  id="box-{$voorkeur->getVanUid()}-{$crid}"
		  name="vrk-{$crid}"
		{if isset($uid)} checked="checked"{/if} />
	</a>
</td>
{/strip}
