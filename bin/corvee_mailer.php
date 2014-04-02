#!/usr/bin/php
<?php 
error_reporting(E_ALL);

/* Usage:
 *
 * http://csrdelft.nl/tools/corvee_mailer.php
 * of
 * /path/to/csrdelft/bin/corvee_mailer.php
 *
 * Het uitvoeren van dit bestand kan in een cronjob gezet worden, die 1x per dag
 * draait.
 */


session_id('maaltrack-cli');

# instellingen & rommeltjes
chdir('../lib/');
require_once 'configuratie.include.php';

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
