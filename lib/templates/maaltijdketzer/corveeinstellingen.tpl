{assign var='actief' value='corveeinstellingen'}
{include file='maaltijdketzer/menu.tpl'}
{$melding}


<h1>Corveeinstellingen</h1>

<form action="/actueel/maaltijden/corveeinstellingen" id="instellingenForm" class="instellingenForm" method="post">
	{foreach from=$instellingen->getFields() item=field}
		{$field->view()}
	{/foreach}
	<div class="submit">
		<label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
	</div>
</form>

