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
	document.getElementById('beginMoment').value=begin;
	document.getElementById('eindMoment').value=einde;
}
function toggleTijden(){
	if(document.getElementById('heledag').checked==true){
		document.getElementById('tijden').style.display='none';
	}else{
		document.getElementById('tijden').style.display='block';
	}
}

</script>
{/literal}
<form method="post" class="agendaitem">
	<label for="titel">Titel</label> 
		<input type="text" id="titel" name="titel" value="{$item->getTitel()}" /><br /><br />

	<label for="datum">Datum</label>
	<input type="text" id="datum" name="datum" value="{$item->getBeginMoment()|date_format:"%Y-%m-%d"}" /><br /><br />
	
	<label for="heledag">Hele dag?</label>
	<input type="checkbox" id="heledag" name="heledag" onclick="return toggleTijden()" {if $item->isHeledag()}checked="checked"{/if} /><br /><br />
	
	<div id="tijden" {if $item->isHeledag()}class="verborgen"{/if}>
		<label for="beginMoment">Tijden:</label> 
			<input type="text" id="beginMoment" name="beginMoment" value="{$item->getBeginMoment()|date_format:"%H:%M"}" class="tijd" /> -
			<input type="text" id="eindMoment" name="eindMoment" value="{$item->getEindMoment()|date_format:"%H:%M"}" class="tijd" />
			<div class="standaardtijden">
				&laquo; <a onclick="setTijd('09:00', '17:30');">Dag</a> <br />
				&laquo; <a onclick="setTijd('18:30', '22:30');">Kring</a> <br />
				&laquo; <a onclick="setTijd('20:00', '22:00');">Avond</a>
			</div>
			<br /><br />
	</div>
	<label for="beschrijving">Beschrijving</label> 
		<input type="text" id="beschrijving" name="beschrijving" value="{$item->getBeschrijving()}" /><br /><br />	
			
	<label for="submit">&nbsp;</label>
	<input type="submit" name="submit" value="Opslaan" /> 
	<a class="knop" href="/actueel/agenda/{$item->getBeginMoment()|date_format:"%Y-%m"}">terug</a>
</form>
