<form name="CSVForm" action="{Instellingen::get('taken', 'url')}/upload" method="post" enctype="multipart/form-data">
	<label for="CSVSaldi">CSV-bestand uploaden</label> 
	<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" />
	<input type="submit" name="submit" value="Uploaden" />
</form>
<br />
<h2>Boekjaar sluiten</h2>
<p>De maaltijden van het boekjaar zullen naar het archief worden verplaatst.</p>
<a href="{Instellingen::get('taken', 'url')}/sluitboekjaar" title="Boekjaar afsluiten" class="knop post modal">{icon get="door_in"} Sluit boekjaar</a>