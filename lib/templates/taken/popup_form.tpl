{*
	popup_form.tpl	| 	P.W.G. Brussee (brussee@live.nl)
*}
<div id="taken-popup" class="outer-shadow dragobject">
<h1>{$view->getTitel()}</h1>
{$view->getMelding()}
<br />
{$form->view()}
<div id="taken-popup-buttons">
{if isset($bijwerken)}
	<a onclick="taken_submit_form($('#{$form->getFormId()}'), true, '{$bijwerken}');" title="Alle eigenschappen overschrijven" class="knop">{icon get="disk_multiple"} Alles bijwerken</a>
{/if}
	<a onclick="taken_submit_form($('#{$form->getFormId()}'), {if isset($nocheck)}true{else}false{/if});" title="Invoer opslaan" class="knop">{icon get="disk"} Opslaan</a>
	<a onclick="taken_reset_form($('#{$form->getFormId()}'));" title="Reset naar opgeslagen gegevens" class="knop">{icon get="arrow_rotate_anticlockwise"} Reset</a>
	<a onclick="taken_close_popup();" title="Annuleren" class="knop">{icon get="delete"} Annuleren</a>
</div>
</div>