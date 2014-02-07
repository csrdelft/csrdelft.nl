{$view->getMelding()}
<h1>Agenda-item {$actie}</h1>
{if $actie == 'toevoegen'}
	<p>Vul de onderstaande velden in om een item toe te voegen aan de agenda.</p>
{elseif $actie == 'bewerken'}
	<p>Bewerk de onderstaande velden om een item te wijzigen in de agenda.</p>
{/if}
{literal}
	<script type="text/javascript">
		function setTijd(begin, einde) {
			document.getElementById('beginMoment').value = begin;
			document.getElementById('eindMoment').value = einde;
		}
		function toggleTijden() {
			if (document.getElementById('heledag').checked == true) {
				document.getElementById('tijden').style.display = 'none';
			} else {
				document.getElementById('tijden').style.display = 'block';
			}
		}
	</script>
{/literal}
<form method="post" class="agendaitem">
	<label for="titel">Titel</label> 
	<input type="text" id="titel" name="titel" value="{$item->titel}" /><br /><br />

	<label for="datum">Datum</label>
	<input type="text" id="datum" name="datum" value="{$item->begin_moment|date_format:"%Y-%m-%d"}" /><br /><br />

	<label for="heledag">Hele dag?</label>
	<input type="checkbox" id="heledag" name="heledag" onclick="return toggleTijden()" {if $item->isHeledag()}checked="checked"{/if} /><br /><br />

	<div id="tijden" {if $item->isHeledag()}class="verborgen"{/if}>
		<label for="beginMoment">Tijden:</label> 
		<input type="text" id="beginMoment" name="begin" value="{$item->begin_moment|date_format:"%H:%M"}" class="tijd" /> -
		<input type="text" id="eindMoment" name="eind" value="{$item->eind_moment|date_format:"%H:%M"}" class="tijd" />
		<div class="standaardtijden">
			&laquo; <a onclick="setTijd('09:00', '17:30');">Dag</a><br />
			&laquo; <a onclick="setTijd('18:30', '23:00');">Kring</a><br />
			&laquo; <a onclick="setTijd('20:00', '23:59');">Avond</a><br />
			&laquo; <a onclick="setTijd('20:00', '22:00');">Lezing</a><br />
		</div>
	</div><br /><br />

	<label for="beschrijving">Beschrijving</label> 
	<textarea id="beschrijving" name="beschrijving">{$item->getBeschrijving()}</textarea><br /><br />	

	<label for="submit">&nbsp;</label>
	<input type="submit" name="submit" value="Opslaan" /> 
	<a class="knop" href="/actueel/agenda/maand/{$item->begin_moment|date_format:"%Y-%m"}">Terug</a>
</form>