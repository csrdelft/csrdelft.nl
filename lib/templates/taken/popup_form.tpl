{*
	popup_form.tpl	| 	P.W.G. Brussee (brussee@live.nl)
*}
<div id="popup-content">
<h1>{$view->getTitel()}</h1>
{SimpleHtml::getMelding()}
<br />
{$form->view()}
{if isset($bijwerken)}
<!-- FIXME a onclick="form_submit();" title="Alle eigenschappen overschrijven" class="knop">{icon get="disk_multiple"} Alles bijwerken</a -->
{/if}
</div>