#!/usr/bin/php5
<?php
/**
 * cron.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor uitvoeren van CRON-jobs.
 * 
 * 'geinstalleerd' met:
 * svn:executable property
 * export EDITOR=nano
 * crontab -e
 * 0 1 * * * /usr/www/csrdelft.nl/bin/cron.php >> /srv/www/csrdelft.nl/data/log/cron.log 2>&1
 * test door ./cron.php te typen
 * 
 * @see http://www.cronjob.nl/
 */
session_id('cron-cli');

chdir('/srv/www/csrdelft.nl/lib/');

require_once 'configuratie.include.php';

// Corvee herinneringen
try {
	require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
	$verstuurd_errors = CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	#TODO: logging
}
