{*
	popup_form.tpl	| 	P.W.G. Brussee (brussee@live.nl)
*}
<h1>{$view->getTitel()}</h1>
{$view->getMelding()}
<br />
{$form->view()}
{if isset($bijwerken)}
	<a onclick="form_submit();" title="Alle eigenschappen overschrijven" class="knop">{icon get="disk_multiple"} Alles bijwerken</a>
{/if}