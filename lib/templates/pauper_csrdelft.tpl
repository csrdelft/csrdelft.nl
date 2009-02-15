<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>C.S.R. Delft | {$csrdelft->getTitel()}</title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft" />
	<meta name="robots" content="index, follow" />
	{foreach from=$csrdelft->getStylesheets() item=sheet}
		<link rel="stylesheet" href="/layout/{$sheet.naam}?{$sheet.datum}" type="text/css" />
	{/foreach}
	{foreach from=$csrdelft->getScripts() item=script}
		<script type="text/javascript" src="/layout/{$script.naam}?{$script.datum}"></script>
	{/foreach}
	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/communicatie/forum/rss.xml" />
	<link rel="shortcut icon" href="{$csr_pics}layout/favicon.ico" />
</head>
{$csrdelft->_body->view()}
{if $lid->hasPermission('P_LOGGED_IN') }
	<div id="ingelogd">
		{$lid->getUid|csrnaam} | <a href="/logout.php">log&nbsp;uit</a>
	</div>
	<form method="post" action="/communicatie/lijst.php?mobile">
		<p>
			<input type="hidden" name="a" value="zoek" />
			<input type="hidden" name="waar" value="naam" />
			<input type="hidden" name="moot" value="alle" />
			<input type="hidden" name="status" value="(oud)?leden" />
			<input type="hidden" name="sort" value="achternaam" />
			<input type="hidden" name="kolom[]" value="adres" />
			<input type="hidden" name="kolom[]" value="email" />
			<input type="hidden" name="kolom[]" value="telefoon" />
			<input type="hidden" name="kolom[]" value="mobiel" />
			Leden zoeken: {if isset($smarty.post.wat)}
				<input type="text" value="{$smarty.post.wat|escape:'htmlall'}" name="wat" id="zoekveld" />
			{else}
				<input type="text" value="naam zoeken" onfocus="this.value=''; this.style.textAlign='left';" name="wat" id="zoekveld" />
			{/if}
			<input type="submit" value="zoeken" />
		</p>
	</form>
{else}
<div id="login">		
	Inloggen:	
	{if isset($smarty.session.auth_error)}
		<span class="waarschuwing">{$smarty.session.auth_error}</span>
	{/if}
	<form action="/login.php" method="post">
		<fieldset>
			<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
			Naam: <input type="text" name="user" value="naam" /><br />
			Wachtwoord: <input type="password" name="pass" value="" /><br />
			<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" checked="checked" />
			<label for="login-checkip">Koppel IP</label><br /><br />
			<input type="submit" name="submit" value="Inloggen" />
		</fieldset>
	</form>			
</div>
{/if}



<div id="footer">
	Gemaakt door <a href="mailto:pubcie@csrdelft.nl" title="PubCie der C.S.R. Delft">PubCie der C.S.R. Delft</a> | <a href="http://validator.w3.org/check/referrer" title="Valideer">XHTML 1.0</a>
</div>	
</body>

</html>
