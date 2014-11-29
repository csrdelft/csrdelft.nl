<form name="CSVForm" action="{$smarty.const.maalcieUrl}/upload" method="post" enctype="multipart/form-data">
	<label for="CSVSaldi">CSV-bestand uploaden</label> 
	<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" />
	<input type="submit" name="submit" value="Uploaden" />
</form>
<br />
<h3>Boekjaar sluiten</h3>
<p>De maaltijden van het boekjaar zullen naar het archief worden verplaatst.</p>
<a href="{$smarty.const.maalcieUrl}/sluitboekjaar" title="Boekjaar afsluiten" class="btn post popup">{icon get="door_in"} Sluit boekjaar</a>