{*
	instelling_row.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="instelling-row-{$id}">
	<td>
		<a title="Instelling wijzigen" class="btn wijzigknop" onclick="
			if (confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
				var form = $('#form-{$id}');
				window.formulier.formInlineToggle(form);
				form.find('.InstellingToggle').toggle();
				$(this).toggle();
			}
		   ">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$id|replace:'_':' '}</nobr></td>
	<td>
		<div class="InlineForm">
			<div class="InstellingToggle">{$waarde}</div>
			<form id="form-{$id}" method="post" action="/instellingenbeheer/opslaan/{$module}/{$id}" class="Formulier InlineForm ToggleForm">
				<textarea name="waarde" origvalue="{htmlspecialchars($waarde)}" class="FormElement" rows="1">{$waarde}</textarea>
				<a class="btn submit" title="Wijzigingen opslaan">{icon get="accept"} Opslaan</a>
				<a class="btn reset cancel" title="Annuleren" onclick="
					$(this).parent().find('.InstellingToggle').toggle();
					$('#instelling-row-{$id}').find('.wijzigknop').toggle();
				">{icon get="delete"} Annuleren</a>
			</form>
		</div>
	</td>
	<td class="col-del">
		<a href="/instellingenbeheer/reset/{$module}/{$id}" title="Instelling resetten" class="btn post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>
{/strip}