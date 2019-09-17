<tr id="instelling-row-{{$id}}">
					<td>
						<a title="Instelling wijzigen" class="btn wijzigknop" onclick="
							if (confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
							var form = $('#form-{{$id}}');
							window.formulier.formInlineToggle(form);
							form.parent().find('.InstellingToggle').toggle();
							$(this).toggle();
							}
							">@icon('pencil')</a>
</td>
<td>{{str_replace('_', ' ', $id)}}</td>
<td>
	<div class="InlineForm">
		<div class="InstellingToggle">{{$waarde}}</div>
		<form id="form-{{$id}}" method="post" action="/instellingenbeheer/opslaan/{{$module}}/{{$id}}"
					class="Formulier InlineForm ToggleForm">
			@php(printCsrfField())
			<textarea class="form-control FormElement" name="waarde" origvalue="{{htmlspecialchars($waarde)}}"
								rows="1">{{$waarde}}</textarea>
			<div class="input-group">
				<a class="btn submit" title="Wijzigingen opslaan">@icon('accept') Opslaan</a>
				<a class="btn reset cancel" title="Annuleren" onclick="
					$(this).parents('.InlineForm').find('.InstellingToggle').toggle();
					$('#instelling-row-{{$id}}').find('.wijzigknop').toggle();
					">
					@icon('delete') Annuleren
				</a>
			</div>
		</form>
	</div>
</td>
<td class="col-del">
	<a href="/instellingenbeheer/reset/{{$module}}/{{$id}}" title="Instelling resetten"
		 class="btn post confirm">@icon('arrow_rotate_anticlockwise')</a>
</td>
</tr>
