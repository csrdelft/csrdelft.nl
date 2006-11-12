<h1>Maaltijden {$actie}</h1>

<form action="/maaltijden/beheer.php" method="post">
	<input type="hidden" name="maalid" value="{$maal.id}" />
	
	<input type="submit" name="submit" value="opslaan" />
</form>
