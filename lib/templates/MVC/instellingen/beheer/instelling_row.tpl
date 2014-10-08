{* instelling_row.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{strip}
<tr id="instelling-row-{$instelling->instelling_id}">
	<td>
		<a title="Instelling wijzigen" class="knop rounded wijzigknop" onclick="
			if (confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
				var form = $('#form-{$instelling->instelling_id}');
				form_inline_toggle(form);
				form.find('.InstellingToggle').toggle();
				$(this).toggle();
			}
		">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$instelling->instelling_id|replace:'_':' '}</nobr></td>
	<td>
		<form id="form-{$instelling->instelling_id}" method="post" action="/instellingenbeheer/opslaan/{$instelling->module}/{$instelling->instelling_id}" class="Formulier InlineForm">
			<div class="InstellingToggle">{$instelling->waarde}</div>
			<div class="InstellingToggle verborgen">&nbsp;</div>
			<div class="InputField">
				<textarea name="waarde" origvalue="{htmlspecialchars($instelling->waarde)}" class="FormElement" rows="1">{$instelling->waarde}</textarea>
			</div>
			<div class="InstellingToggle verborgen"></div>
			<div class="FormButtons">
				<a class="knop submit confirm" title="Wijzigingen opslaan">{icon get="accept"} Opslaan</a>
				<a class="knop reset cancel" title="Annuleren" onclick="
					$(this).parent().find('.InstellingToggle').toggle();
					$(this).parent().parent().parent().find('.wijzigknop').toggle();
				">{icon get="delete"} Annuleren</a>
			</div>
		</form>
	</td>
	<td class="col-del">
		<a href="/instellingenbeheer/reset/{$instelling->module}/{$instelling->instelling_id}" title="Instelling resetten" class="knop rounded post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>
{/strip}