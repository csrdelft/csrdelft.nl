{assign var='actief' value='saldi'}
{include file='maaltijdketzer/menu.tpl'}

<h1>MaalCie-saldi invoeren met een CSV-bestand.</h1>
{if $status!=''}
	<div class="waarschuwing">{$status}</div><br />
{/if}
<form name="CSVForm" action="saldi.php" method="post" enctype="multipart/form-data">
	<label for="CSVSaldi">CSV-bestand uploaden</label> 
	<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" /><br />
	<input type="submit" name="submit" value="uploaden" />
</form>
