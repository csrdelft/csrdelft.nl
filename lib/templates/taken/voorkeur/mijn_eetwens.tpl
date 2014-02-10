{*
	mijn_eetwens.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="eetwens" style="display: inline-block; min-width: 250px; vertical-align: top;">
	<div class="inline-edit" onclick="taken_toggle_hiddenform(this);">{$eetwens|truncate:50:"...":true}&nbsp;</div>
	<form method="post" action="{$instellingen->get('taken', 'url')}/eetwens" class="Formulier taken-hidden-form taken-subform">
		<textarea name="eetwens" origvalue="{$eetwens}" class="FormField">{$eetwens}</textarea><br />
		<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"} Opslaan&nbsp;</a>
		<a onclick="taken_toggle_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"} Annuleren&nbsp;</a>
	</form>
</div>