{*
	instelling_row.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="instelling-row-{$instelling->instelling_id}">
	<td>
		<a title="Instelling wijzigen" class="knop" onclick="
			if (confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
				taken_toggle_hiddenform($(this).parent());
			}
		">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$instelling->instelling_id|replace:'_':' '}</nobr></td>
	<td>
		<div>{$instelling->waarde}</div>
		<form method="post" action="/instellingenbeheer/opslaan/{$instelling->module}/{$instelling->instelling_id}" class="Formulier taken-hidden-form taken-subform">
			<textarea name="waarde" origvalue="{htmlspecialchars($instelling->waarde)}" class="FormField" rows="1">{$instelling->waarde}</textarea>
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="taken_toggle_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	</td>
	<td class="col-del">
		<a href="/instellingenbeheer/reset/{$instelling->module}/{$instelling->instelling_id}" title="Instelling resetten" class="knop post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>
{/strip}
