#!/etc/php5/cgi
<?php
/**
 * cron.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor uitvoeren van CRON-jobs.
 * 
 * 'geinstalleerd' met: (chmod bij elke svn up!!!)
 * chmod +x /usr/www/csrdelft.nl/bin/cron.php
 * 0 1 * * * /usr/www/csrdelft.nl/bin/cron.php >> cron.log 2>&1
 * test door ./cron.php te typen
 */
session_id('cron-cli');

chdir('../lib/');
require_once 'configuratie.include.php';

// Corvee herinneringen
try {
	require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
	$verstuurd_errors = CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	#TODO: logging
}
