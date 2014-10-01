{*
	beheer_punten_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="punten-row-{$puntenlijst.lid->getUid()}">
	<td>{$puntenlijst.lid->getNaamLink(Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}</td>
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
		<form action="{Instellingen::get('taken', 'url')}/wijzigpunten/{$puntenlijst.lid->getUid()}" method="post" class="Formulier InlineForm">
			<div class="InlineFormToggle">{$puntenlijst.puntenTotaal}</div>
			<div class="InputField">
				<input type="text" name="totaal_punten" value="{$puntenlijst.puntenTotaal}" origvalue="{$puntenlijst.puntenTotaal}" class="FormElement" maxlength="4" size="4" />
			</div>
			<div class="FormButtons">
				<a class="knop submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="knop reset cancel" title="Annuleren" >{icon get="delete"}</a>
			</div>
		</form>
	</td>
	<td>
		<form action="{Instellingen::get('taken', 'url')}/wijzigbonus/{$puntenlijst.lid->getUid()}" method="post" class="Formulier InlineForm">
			<div class="InlineFormToggle">{$puntenlijst.bonusTotaal}</div>
			<div class="InputField">
				<input type="text" name="totaal_bonus" value="{$puntenlijst.bonusTotaal}" origvalue="{$puntenlijst.bonusTotaal}" class="FormElement" maxlength="4" size="4" />
			</div>
			<div class="FormButtons">
				<a class="knop submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="knop reset cancel" title="Annuleren">{icon get="delete"}</a>
			</div>
		</form>
	</td>
	<td style="text-align: right; background-color: #{$puntenlijst.prognoseColor};"{if $puntenlijst.vrijstelling} title="{$puntenlijst.vrijstelling->getPercentage()}% vrijstelling"{/if}>
		{$puntenlijst.prognose}
		<div class="inline" style="width: 25px;">{if $puntenlijst.vrijstelling}*{else}&nbsp;{/if}</div>
	</td>
</tr>