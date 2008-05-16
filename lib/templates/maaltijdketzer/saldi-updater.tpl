<div style="float: right; margin: 0 0 10px 10px;">
	<a href="/actueel/maaltijden/voorkeuren.php" title="Instellingen">Instellingen</a>
	{if $lid->hasPermission('P_MAAL_MOD')}
		| <a href="/actueel/maaltijden/beheer/" title="Beheer">Beheer</a>
		| <a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
	{/if}
</div>
<h1>MaalCie-saldi invoeren met een CSV-bestand.</h1>
{if $status!=''}
	<div class="waarschuwing">{$status}</div><br />
{/if}
<form name="CSVForm" action="saldi.php" method="post" enctype="multipart/form-data">
	<label for="CSVSaldi">CSV-bestand uploaden</label> 
	<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" /><br />
	<input type="submit" name="submit" value="uploaden" />
</form>