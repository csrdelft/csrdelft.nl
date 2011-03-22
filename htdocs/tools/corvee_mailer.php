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

if(!($loginlid->hasPermission('P_ADMIN') || $loginlid->hasPermission('P_MAALCIE'))){
	header('location: '.CSR_ROOT);
	exit;
}

require_once 'maaltijden/maaltrack.class.php';
$maaltrack = new MaalTrack();

$debugMode = (isset($_POST['debug']) ? (int)$_POST['debug'] : null);
$debugAddr = (isset($_POST['debugAddr']) ? $_POST['debugAddr'] : 'pubcie@csrdelft.nl');

if (isset($_POST['submit']))
{
	$maaltrack->corveeAutoMailer($debugMode, $debugAddr);
	echo '<strong>Klaar!</strong><br /><br />';
}

echo '<form method="post">
	<label>Debug</label><input type="checkbox" name="debug" value="1" '.($debugMode?'checked="checked"':'').' /><br />
	<label>DebugAddr</label><input type="text" name="debugAddr"  value="'.$debugAddr.'"/><br />
	<input type="submit" name="submit" value="Verzenden" />';

?>
