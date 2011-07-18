{$melding}

	<h2>Boek toevoegen</h2>
	<p>
		Voer de gegevens in van je boek.
	</p>
<p>
	Als er <span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
	betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten moeten aanpassen aan het
	gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.
</p>

<form action="/communicatie/bibliotheek/addboek/" id="profielForm" method="post">
	{foreach from=$boek->getFields() item=field}
		{$field->view()}
	{/foreach}
	<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
		<input type="reset" value="reset formulier" />
		<a class="knop" href="/communicatie/bibliotheek/">Annuleren</a>
	</div>
</form>
