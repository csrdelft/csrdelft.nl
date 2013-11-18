{*
	popup_form.tpl	| 	P.W.G. Brussee (brussee@live.nl)
*}
<div id="taken-popup" class="outer-shadow">
<h1>{$kop}</h1>
{$melding}<br />
{$form->view()}
<div id="taken-popup-buttons">
{if isset($bijwerken)}
	<a href="javascript:void(0);" onclick="taken_submit_form('#{$form->getFormId()}', '{$bijwerken}');" title="Alles bijwerken" class="knop">{icon get="disk_multiple"} Alles bijwerken</a>
{/if}
	<a href="javascript:void(0);" onclick="$('#{$form->getFormId()}').submit();" title="Invoer opslaan" class="knop">{icon get="disk"} Opslaan</a>
	<a href="javascript:void(0);" onclick="taken_reset('#{$form->getFormId()}');" title="Invoer ongedaan maken" class="knop">{icon get="arrow_rotate_anticlockwise"} Reset</a>
	<a href="javascript:void(0);" onclick="close_taken_popup();" title="Annuleren" class="knop">{icon get="delete"} Annuleren</a>
</div>
</div>