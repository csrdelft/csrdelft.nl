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

require_once 'taken/HerinneringenModel.class.php';
Taken\CRV\HerinneringenModel::stuurHerinneringen();

?>
