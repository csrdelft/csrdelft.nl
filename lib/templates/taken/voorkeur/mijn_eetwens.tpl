{*
	mijn_eetwens.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="eetwens" style="display: inline-block; min-width: 250px; vertical-align: top;">
	<div class="inline-edit" onclick="toggle_taken_hiddenform(this);$('#eetwens-field').focus();">{$eetwens|truncate:50:"...":true}&nbsp;</div>
	<form method="post" action="{$module}/eetwens" class="Formulier taken-hidden-form taken-subform">
		<textarea id="eetwens-field" name="eetwens">{$eetwens}</textarea><br />
		<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"} Opslaan&nbsp;</a>
		<a onclick="toggle_taken_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"} Annuleren&nbsp;</a>
	</form>
</div>