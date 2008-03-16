<form action="/groepen/{$gtype}/{$groep->getId()}/bewerken" method="post">
<div class="groepAdmin" style="width: 100%; clear: both;">
	{if $groep->isAdmin()}
		{if $groep->getId()==0}
			<h2>Nieuwe groep toevoegen in context {$gtype}</h2>
			
			<strong>Korte naam:</strong><br />
			Voor gebruik in urls &eacute;n ter sortering. Alleen letters en cijfers, geen spaties. Voor elkaar opvolgende groepen dezelfde naam gebruiken.<br />
			<input type="text" name="snaam" style="width: 100%" value="{$groep->getSnaam()|escape:'html'}" />
		<br />
		{/if}
		<strong>Naam:</strong><br />
		<input type="text" name="naam" style="width: 100%" value="{$groep->getNaam()|escape:'html'}" />
		<br />
		<strong>Status:</strong> <select name="status">
			<option value="ht" {if $groep->getStatus()=="ht"}selected="selected"{/if}>h.t.</option>
			<option value="ot" {if $groep->getStatus()=="ot"}selected="selected"{/if}>o.t.</option>
			<option value="ft" {if $groep->getStatus()=="ft"}selected="selected"{/if}>f.t.</option>
		</select>
		
		<strong>Periode:</strong> 
		<input type="text" name="begin" value="{$groep->getBegin()}" /> - <input type="text" name="einde" value="{$groep->getEinde()}" />
		<br /><br />
		<strong>Korte beschrijving:</strong><br />
		<textarea name="sbeschrijving" style="width: 100%; height: 100px;">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		<br />
	{/if}
	<strong>Lange beschrijving:</strong><br />
	<textarea name="beschrijving" style="width: 100%; height: 200px;">{$groep->getBeschrijving()|escape:'html'}</textarea>
	<input type="submit" value="Opslaan" /> <a href="/groepen/{$gtype}/{$groep->getId()}/" class="knop">terug</a>
</div>
</form>