{if $actie == 'toevoegen'}
	<h1>Agenda-item toevoegen</h1>
	Vul de onderstaande velden in om een item toe te voegen aan de agenda.<br /><br />
{elseif $actie == 'bewerken'}
	<h1>Agenda-item bewerken</h1>
{/if}

{$melding}

<form method="post" class="agendaitem">
	<label for="titel">Titel</label> 
			<input type="text" id="titel" name="titel" value="{$item->getTitel()}" /><br /><br />

	<label for="beginMoment">Beginmoment</label> 
			<input type="text" id="beginMoment" name="beginMoment" value="{$item->getBeginMoment()|date_format:"%Y-%m-%d %H:%M"}" /><br /><br />
	
	<label for="eindMoment">Eindmoment</label> 
			<input type="text" id="eindMoment" name="eindMoment" value="{$item->getEindMoment()|date_format:"%Y-%m-%d %H:%M"}" /><br /><br />
			
	<label for="beschrijving">Beschrijving</label> 
			<input type="text" id="beschrijving" name="beschrijving" value="{$item->getBeschrijving()}" /><br /><br />	
			
	<input type="submit" name="submit" value="Opslaan" />
</form>