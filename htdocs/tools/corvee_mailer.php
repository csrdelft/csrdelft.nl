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

$debugMode = (isset($_GET['debug']) ? (int)$_GET['debug'] : null);
$debugAddr = (isset($_GET['debugAddr']) ? $_GET['debugAddr'] : 'pubcie@csrdelft.nl');

$maaltrack->corveeAutoMailer($debugMode, $debugAddr);

?>
