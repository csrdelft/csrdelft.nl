{*
	beheer_punten_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="punten-row-{$puntenlijst.lid->getUid()}">
	<td>{$puntenlijst.lid->getNaamLink($instellingen->get('corvee', 'weergave_ledennamen_beheer'), $instellingen->get('corvee', 'weergave_link_ledennamen'))}</td>
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
		<div class="inline-edit" onclick="form_inline_toggle(this);">{$puntenlijst.puntenTotaal}</div>
		<form method="post" action="{$instellingen->get('taken', 'url')}/wijzigpunten/{$puntenlijst.lid->getUid()}" class="Formulier InlineForm">
			<input type="text" name="totaal_punten" value="{$puntenlijst.puntenTotaal}" origvalue="{$puntenlijst.puntenTotaal}" class="FormField" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="form_inline_toggle($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	</td>
	<td>
		<div class="inline-edit" onclick="form_inline_toggle(this);">{$puntenlijst.bonusTotaal}</div>
		<form method="post" action="{$instellingen->get('taken', 'url')}/wijzigbonus/{$puntenlijst.lid->getUid()}" class="Formulier InlineForm">
			<input type="text" name="totaal_bonus" value="{$puntenlijst.bonusTotaal}" origvalue="{$puntenlijst.bonusTotaal}" class="FormField" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="form_inline_toggle($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	</td>
	<td style="text-align: right; background-color: #{$puntenlijst.prognoseColor};"{if $puntenlijst.vrijstelling} title="{$puntenlijst.vrijstelling->getPercentage()}% vrijstelling"{/if}>
		{$puntenlijst.prognose}
		<div style="display: inline-block; width: 25px;"{if $puntenlijst.vrijstelling}>*{else}>&nbsp;{/if}</div>
		</div>
	</td>
</tr>