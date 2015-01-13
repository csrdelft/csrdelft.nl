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
chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

$start = microtime(true);

// Debuglog
try {
	DebugLogModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'DebugLogModel::opschonen()', array(), $e);
}

// VerifyModel
try {
	OneTimeTokensModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'VerifyModel::opschonen()', array(), $e);
}

// Instellingen
try {
	Instellingen::instance()->opschonen();
	LidInstellingen::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', '(Lid)Instellingen::instance()->opschonen()', array(), $e);
}

// Corvee herinneringen
try {
	require_once 'model/maalcie/CorveeHerinneringenModel.class.php';
	CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'CorveeHerinneringenModel::stuurHerinneringen()', array(), $e);
}

// Forum opschonen
try {
	require_once 'model/ForumModel.class.php';
	ForumModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'ForumModel::instance()->opschonen()', array(), $e);
}

$finish = microtime(true) - $start;
if (DEBUG) {
	echo getDateTime() . ' Finished in ' . (int) $finish . " seconds.\r\n";
}