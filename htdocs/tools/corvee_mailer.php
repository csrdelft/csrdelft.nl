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

if(!(LoginLid::mag('P_ADMIN') || LoginLid::mag('P_MAAL_MOD'))){
	header('location: '.CSR_ROOT);
	exit;
}

try {
	require_once 'taken/controller/BeheerTakenController.class.php';
	$controller = new BeheerTakenController();
	$controller->herinneren();
	$controller->getContent()->view();
}
catch (\Exception $e) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::mag('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>
