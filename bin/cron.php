#!/usr/bin/php
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
use CsrDelft\model\DebugLogModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\LogModel;
use CsrDelft\model\maalcie\CorveeHerinneringenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\OneTimeTokensModel;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

$start = microtime(true);

// Debuglog
try {
	DebugLogModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'DebugLogModel::opschonen()', array(), $e);
}

// Log
try {
	LogModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'LogModel::opschonen()', array(), $e);
}

// LoginModel
try {
	LoginModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'LoginModel::opschonen()', array(), $e);
}

// VerifyModel
try {
	OneTimeTokensModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'VerifyModel::opschonen()', array(), $e);
}

// Instellingen
try {
	InstellingenModel::instance()->opschonen();
	LidInstellingenModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', '(Lid)InstellingenModel::instance()->opschonen()', array(), $e);
}

// Corvee herinneringen
try {
	CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'CorveeHerinneringenModel::stuurHerinneringen()', array(), $e);
}

// Forum opschonen
try {
	ForumModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'ForumModel::instance()->opschonen()', array(), $e);
}

try {
    passthru('php ../bin/cron/pin_transactie_download.php');
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'php pin_transactie_download.php', array(), $e);
}

$finish = microtime(true) - $start;
if (DEBUG) {
	echo getDateTime() . ' Finished in ' . (int)$finish . " seconds.\r\n";
}
