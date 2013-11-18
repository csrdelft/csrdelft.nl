{*
	beheer_punten_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="punten-row-{$puntenlijst.lid->getUid()}">
	<td>{$puntenlijst.lid->getNaamLink('civitas', 'link')}</td>
{foreach from=$puntenlijst.aantal key=fid item=aantal}
	<td>{if $aantal !== 0}{$puntenlijst.punten[$fid]}{/if}{if $puntenlijst.bonus[$fid] > 0}+{/if}{if $puntenlijst.bonus[$fid] !== 0}{$puntenlijst.bonus[$fid]}{/if}{if $aantal !== 0} ({$aantal}){/if}</td>
{/foreach}
	<td>
		<div class="inline-edit" onclick="toggle_taken_hiddenform(this);">{$puntenlijst.puntenTotaal}</div>
		<form method="post" action="{$module}/wijzigpunten" class="Formulier taken-hidden-form taken-subform">
			<input type="hidden" name="voor_lid" value="{$puntenlijst.lid->getUid()}" />
			<input type="text" name="totaal_punten" value="{$puntenlijst.puntenTotaal}" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="toggle_taken_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	</td>
	<td>
		<div class="inline-edit" onclick="toggle_taken_hiddenform(this);">{$puntenlijst.bonusTotaal}</div>
		<form method="post" action="{$module}/wijzigbonus" class="Formulier taken-hidden-form taken-subform">
			<input type="hidden" name="voor_lid" value="{$puntenlijst.lid->getUid()}" />
			<input type="text" name="totaal_bonus" value="{$puntenlijst.bonusTotaal}" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="toggle_taken_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	</td>
	<td style="background-color: #{$puntenlijst.prognoseColor}">{$puntenlijst.prognose}</td>
</tr>