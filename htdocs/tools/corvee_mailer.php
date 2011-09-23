<?php 

/* Usage:
 *
 * http://csrdelft.nl/tools/corvee_mailer.php
 * of
 * /path/to/csrdelft/bin/corvee_mailer.php
 *
 * Het uitvoeren van dit bestand kan in een cronjob gezet worden, die 1x per dag
 * draait.
 *
 * Gebruik eventueel corvee_mailer.php?debug=1&debugAddr=foo@bar.com om te testen.
 * Als Debug enabled:
 * - alle emails worden naar het debugAddr gestuurd
 * - maaltijd word niet gemarkeerd als gemaild
 */

require_once 'configuratie.include.php';

if(!($loginlid->hasPermission('P_ADMIN') || $loginlid->hasPermission('P_MAAL_MOD'))){
	header('location: '.CSR_ROOT);
	exit;
}

require_once 'maaltijden/maaltrack.class.php';
$maaltrack = new MaalTrack();

$debugMode = (isset($_POST['debug']) ? (int)$_POST['debug'] : null);
$debugAddr = (isset($_POST['debugAddr']) ? $_POST['debugAddr'] : 'pubcie@csrdelft.nl');
$monthMailing = (isset($_POST['monthMailing']) ? (int)$_POST['monthMailing'] : null);

if (isset($_POST['submit']))
{
	$maaltrack->corveeAutoMailer($debugMode, $debugAddr, $monthMailing);
	echo '<strong>Klaar!</strong><br /><br />';
}

echo '<form method="post">
	<p>Debug: Mails gaan naar debugAddr en maaltijden worden niet gemarkeerd als gemaild<br />
	Maandmailing: Aanvinken stuurt mails naar ingedeelde corveërs binnen de komende 35 dagen. Uitvinken naar ingedeelden binnen komende 7 dagen.</p>
	<label>Debug</label><input type="checkbox" name="debug" value="1" '.($debugMode?'checked="checked"':'').' /><br />
	<label>DebugAddr</label><input type="text" name="debugAddr"  value="'.$debugAddr.'"/><br />
	<label>Maandmailing</label><input type="checkbox" name="monthMailing" value="1" '.($monthMailing?'checked="checked"':'').' /><br />
	<input type="submit" name="submit" value="Verzenden" />';

?>
