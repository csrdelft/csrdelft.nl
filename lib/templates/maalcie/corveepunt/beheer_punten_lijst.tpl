{*
	beheer_punten_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="punten-row-{$puntenlijst.lid->uid}">
	<td>{$puntenlijst.lid->getNaam(CsrDelft\model\InstellingenModel::get('corvee', 'weergave_ledennamen_beheer'))}</td>
{foreach from=$puntenlijst.aantal key=fid item=aantal}
	<td>{strip}
	{if $aantal !== 0}
		{$puntenlijst.punten[$fid]}
	{/if}
	{if $puntenlijst.bonus[$fid] > 0}
		+
	{/if}
	{if $puntenlijst.bonus[$fid] !== 0}
		{$puntenlijst.bonus[$fid]}
	{/if}
	{if $aantal !== 0}
		,{$aantal}
	{/if}
	</td>{/strip}
{/foreach}
	<td>
		<div class="InlineForm">
			<div class="InlineFormToggle">{$puntenlijst.puntenTotaal}</div>
			<form action="{$smarty.const.maalcieUrl}/wijzigpunten/{$puntenlijst.lid->uid}" method="post" class="Formulier InlineForm ToggleForm">
				{printCsrfField()}
				<input type="text" name="totaal_punten" value="{$puntenlijst.puntenTotaal}" origvalue="{$puntenlijst.puntenTotaal}" class="FormElement" maxlength="4" size="4" />
				<a class="btn submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="btn reset cancel" title="Annuleren" >{icon get="delete"}</a>
			</form>
		</div>
	</td>
	<td>
		<div class="InlineForm">
			<div class="InlineFormToggle">{$puntenlijst.bonusTotaal}</div>
			<form action="{$smarty.const.maalcieUrl}/wijzigbonus/{$puntenlijst.lid->uid}" method="post" class="Formulier InlineForm ToggleForm">
				{printCsrfField()}
				<input type="text" name="totaal_bonus" value="{$puntenlijst.bonusTotaal}" origvalue="{$puntenlijst.bonusTotaal}" class="FormElement" maxlength="4" size="4" />
				<a class="btn submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="btn reset cancel" title="Annuleren">{icon get="delete"}</a>
			</form>
		</div>
	</td>
	<td style="text-align: right; background-color: #{$puntenlijst.prognoseColor};"{if $puntenlijst.vrijstelling} title="{$puntenlijst.vrijstelling->percentage}% vrijstelling"{/if}>
		{$puntenlijst.prognose}
		<div class="inline" style="width: 25px;">{if $puntenlijst.vrijstelling}*{else}&nbsp;{/if}</div>
	</td>
</tr>