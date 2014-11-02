<form name="CSVForm" action="{maalcieUrl}/upload" method="post" enctype="multipart/form-data">
	<label for="CSVSaldi">CSV-bestand uploaden</label> 
	<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" />
	<input type="submit" name="submit" value="Uploaden" />
</form>
<br />
<h2>Boekjaar sluiten</h2>
<p>De maaltijden van het boekjaar zullen naar het archief worden verplaatst.</p>
<a href="{maalcieUrl}/sluitboekjaar" title="Boekjaar afsluiten" class="knop post modal">{icon get="door_in"} Sluit boekjaar</a>