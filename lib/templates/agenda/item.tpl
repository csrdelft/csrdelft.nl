{if $actie == 'toevoegen'}
	<h1>Agenda-item toevoegen</h1>
	Vul de onderstaande velden in om een item toe te voegen aan de agenda.<br /><br />
{elseif $actie == 'bewerken'}
	<h1>Agenda-item bewerken</h1>
{/if}

{$melding}
{literal}
<script type="text/javascript">
function setTijd(begin, einde){
	date=document.getElementById('beginMoment').value.substring(0,10);
	
	document.getElementById('beginMoment').value=date+' '+begin;
	document.getElementById('eindMoment').value=date+' '+einde;
}
</script>
{/literal}
<form method="post" class="agendaitem">
	<label for="titel">Titel</label> 
		<input type="text" id="titel" name="titel" value="{$item->getTitel()}" /><br /><br />

	<label for="beginMoment">Beginmoment</label> 
		<input type="text" id="beginMoment" name="beginMoment" value="{$item->getBeginMoment()|date_format:"%Y-%m-%d %H:%M"}" /> 
		<div class="standaardtijden">
			&laquo; <a onclick="setTijd('00:00', '23:59');">Hele dag</a> <br />
			&laquo; <a onclick="setTijd('09:00', '17:30');">Dag</a> <br />
			&laquo; <a onclick="setTijd('18:30', '22:30');">Kring</a> <br />
			&laquo; <a onclick="setTijd('20:00', '22:00');">Avond</a>
		</div>
		
		<br /><br />
	
	<label for="eindMoment">Eindmoment</label> 
		<input type="text" id="eindMoment" name="eindMoment" value="{$item->getEindMoment()|date_format:"%Y-%m-%d %H:%M"}" /><br /><br />
			
	<label for="beschrijving">Beschrijving</label> 
		<input type="text" id="beschrijving" name="beschrijving" value="{$item->getBeschrijving()}" /><br /><br />	
			
	<label for="submit">&nbsp;</label>
	<input type="submit" name="submit" value="Opslaan" /> 
	<a class="knop" href="/actueel/agenda/{$item->getBeginMoment()|date_format:"%Y-%m"}">terug</a>
</form>
