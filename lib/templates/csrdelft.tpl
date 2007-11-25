<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>C.S.R. Delft | {$csrdelft->getTitel()}</title>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content='PubCie C.S.R. Delft, Jan Pieter Waagmeester' />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" href="/layout/undohtml.css" type="text/css" />
	<link rel="stylesheet" href="/layout/default.css" type="text/css" />
	{$csrdelft->getStylesheets()}
	<script type="text/javascript" src="/layout/csrdelft.js" defer="defer"></script>

	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.xml" />
	<link rel="shortcut icon" href="{$csr_pics}layout/favicon.ico" />
</head>
<body>
<div id="layoutContainer">
{$csrdelft->_menu->view()}
<div id="bodyContainer{$csrdelft->getBreed()}">
	<div id="lichaam{$csrdelft->getBreed()}">
		<div id="bodyContent">
			{$csrdelft->_body->view()}
		</div>
	</div>
	<div id="copyright">
		<div id="copyrightContent">
			Gemaakt door <a href="mailto:pubcie@csrdelft.nl">PubCie der C.S.R. Delft</a>. | <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a>
		</div>
	</div>
</div>
{if $csrdelft->_zijkolom!==false}
	<div id="zijkolom">
		<div id="zijkolomContent">
			{if is_object($csrdelft->_zijkolom)}
				{$csrdelft->_zijkolom->view()}
			{else}
				{section name=object loop=$csrdelft->_zijkolom}
					{$object->view()}
				{/section}
			{/if}
		</div>
		<div id="zijkolomFooter"></div>
	</div>
{/if}
<div id="navigatie">
	<div id="navigatieContent">{$csrdelft->viewWaarbenik()}</div>
</div>
<div id="hoofder{$csrdelft->getBreed()}">
	<div id="beeldmerk">
		<a href="/" title="Terug naar het begin"><img src="{$csr_pics}layout/beeldmerk.jpg" alt="Beeldmerk van de Vereniging" /></a>
	</div>
</div>
{if $csrdelft->_lid->hasPermission('P_LOGGED_IN') }
  <div id="searchbox">
  	<form method="post" action="/intern/lijst.php">
  		<p>
				<input type="hidden" name="a" value="zoek" />
				<input type="hidden" name="waar" value="naam" />
				<input type="hidden" name="moot" value="alle" />
				<input type="hidden" name="status" value="leden" />
				<input type="hidden" name="sort" value="achternaam" />
				<input type="hidden" name="kolom[]" value="adres" />
				<input type="hidden" name="kolom[]" value="email" />
				<input type="hidden" name="kolom[]" value="telefoon" />
				<input type="hidden" name="kolom[]" value="mobiel" />
				{if isset($smarty.post.wat)}
					<input type="text" value="{$smarty.post.wat|escape:'htmlall'}" name="wat" />
				{else}
					<input type="text" value="Zoeken in ledenlijst..." onfocus="this.value=''" name="wat" />
				{/if}
			</p>
		</form>
	</div>
{/if}
<div id="personalBox">
	{if $csrdelft->_lid->hasPermission('P_LOGGED_IN') }
		U bent {$csrdelft->_lid->getCivitasName()}<br />
		<a href="/logout.php">log&nbsp;uit</a> | <a href="/intern/profiel/{$csrdelft->_lid->getUid()}">profiel</a>
	{else}
		<a href="#" id="inloglink" onclick="document.getElementById('inloggen').style.display = 'block'">
			Inloggen...
		</a>
		<div id="inloggen">
			{if isset($smarty.session.auth_error)}
				<span class="waarschuwing">{$smarty.session.auth_error}</span>
				<script type="text/javascript">
					<!-- 
					document.getElementById('inloggen').style.display='block';
					-->
				</script>
			{/if}
			<form id="frm_login" action="/login.php" method="post">
				<p style="display: inline;">
					<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
					<input type="text" name="user" class="login" value="Naam" onfocus="this.value=''" />
					<input type="password" name="pass" class="login" />
					<input type="submit" name="submit" class="login-submit" value="ok" /><br />
					<input type="checkbox" name="checkip" class="login-checkip" value="true" id="login-checkip" checked="checked" />
					<label for="login-checkip">Koppel login en IP-adres</label><br />
				</p>
			</form>
		</div>
	{/if}
</div>
<div id="lijntje"><img src="http://plaetjes.csrdelft.nl/layout/pixel.gif" height="1px" width="1px" alt="lijntje..." /></div>
<div id="hoofderFoto"><img src="{$csr_pics}layout/hoofder.jpg" height="130px" alt="een impressie van de Civitas" /></div>
{if is_array($saldi)}
	<div id="uStaatRood">
		<strong>U staat rood:</strong><br />
		{foreach from=$saldi item=saldo}
			Uw saldo bij de {$saldo.naam} is &euro; {$saldo.saldo}.<br />
		{/foreach}
	</div>
{/if}

</div>
<!-- selecteer-bug-fix voor IE ( http://trac.knorrie.org/csrdelft.nl/changeset/158) -->
<script type="text/javascript" defer="defer">
<!--
document.body.style.height = document.documentElement.scrollHeight+'px';
-->
</script>
</body>
</html>
