<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft | {$csrdelft->getTitel()}</title>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" href="/layout/undohtml.css" type="text/css" />
	<link rel="stylesheet" href="/layout/default.css" type="text/css" />
	<link rel="stylesheet" href="/layout/forum.css" type="text/css" />
	<script type="text/javascript" src="/layout/csrdelft.js" ></script>
	<script type="text/javascript" src="/layout/minmax.js"></script>
	<script type="text/javascript" src="/layout/position.js"></script>
	<link rel="alternate" title="C.S.R.-Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.php" />
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
			&copy; 2006 PubCie Waagmeester der C.S.R.-Delft. | <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a>
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
	</div>
{/if}
<div id="navigatie">
	<div id="navigatieContent">{$csrdelft->viewWaarbenik()}</div>
</div>
<div id="hoofder{$csrdelft->getBreed()}">
	<div id="beeldmerk"><a href="/"><img src="/layout/images/csr.jpg" alt="Beeldmerk van de Vereniging" /></a></div>
</div>
{if $csrdelft->_lid->isLoggedIn() }
  <div id="searchbox">
  	<form method="post" action="/leden/lijst.php">
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
	{if $csrdelft->_lid->isLoggedIn() }
		U bent {$csrdelft->_lid->getCivitasName()}<br />
		<a href="/logout.php">log&nbsp;uit</a> | <a href="/leden/profiel/{$csrdelft->_lid->getUid()}">profiel</a>
	{else}
		<a href="#" onclick="document.getElementById('inloggen').style.display = 'block'">
			Inloggen...
		</a>
		<div id="inloggen">
			{if isset($smarty.session.auth_error)}
				<span class="waarschuwing">{$smarty.session.auth_error}</span>
			{/if}
			<form id="frm_login" action="/login.php" method="post">
				<table>
					<tr>
						<td><strong>Inloggen:</strong></td>
						<td>
							<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
							<input type="submit" class="login" value="inloggen" />
						</td>
					</tr>
					<tr>
						<td>Naam:</td>
						<td><input type="text" name="user" class="login" /></td>
					</tr>
					<tr>
						<td>Wachtwoord:</td>
						<td><input type="password" name="pass" class="login" /></td>
					</tr>
				</table>
			</form>
		</div>
	{/if}
</div>
<div id="lijntje"><img src="/layout/images/pixel.gif" height="3px" width="20px" alt="lijntje..." /></div>
<div id="hoofderFoto"><img src="/layout/images/hoofder5.jpg" height="130px" alt="een impressie van de Civitas" /></div>
</div>
</body>
</html>
