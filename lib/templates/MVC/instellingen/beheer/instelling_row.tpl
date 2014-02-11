{*
	instelling_row.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="instelling-row-{$instelling->instelling_id}">
	<td>
		<a title="Instelling wijzigen" class="knop" onclick="
			if (confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
				var form = $('#form-{$instelling->instelling_id}');
				form_inline_toggle(form);
				form.find('.InstellingToggle').toggle();
			}
		">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$instelling->instelling_id|replace:'_':' '}</nobr></td>
	<td>
		<form id="form-{$instelling->instelling_id}" method="post" action="/instellingenbeheer/opslaan/{$instelling->module}/{$instelling->instelling_id}" class="Formulier InlineForm">
			<div class="InstellingToggle">{$instelling->waarde}</div>
			<textarea name="waarde" origvalue="{htmlspecialchars($instelling->waarde)}" class="FormField" rows="1">{$instelling->waarde}</textarea>
			<a class="knop submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
			<a class="knop reset cancel" title="Annuleren" onclick="$(this).parent().find('.InstellingToggle').toggle();">{icon get="delete"}</a>
		</form>
	</td>
	<td class="col-del">
		<a href="/instellingenbeheer/reset/{$instelling->module}/{$instelling->instelling_id}" title="Instelling resetten" class="knop post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>
{/strip}
