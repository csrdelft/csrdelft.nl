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

use CsrDelft\Kernel;
use CsrDelft\model\maalcie\CorveeHerinneringenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\LogRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;

chdir(dirname(__FILE__) . '/../lib/');

/** @var Kernel $kernel */
$kernel = require_once 'configuratie.include.php';
$container = $kernel->getContainer();

$start = microtime(true);

$debugLogRepository = $container->get(DebugLogRepository::class);
$logModel = $container->get(LogRepository::class);
$loginModel = $container->get(LoginModel::class);
$oneTimeTokensModel = $container->get(OneTimeTokensRepository::class);
$instellingenRepository = $container->get(InstellingenRepository::class);
$lidInstellingenRepository = $container->get(LidInstellingenRepository::class);
$corveeHerinneringenModel = $container->get(CorveeHerinneringenModel::class);
$forumCategorieRepository = $container->get(ForumCategorieRepository::class);

// Debuglog
try {
	$debugLogRepository->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'DebugLogModel::opschonen()', array(), $e);
}

// Log
try {
	$logModel->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'LogModel::opschonen()', array(), $e);
}

// LoginModel
try {
	$loginModel->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'LoginModel::opschonen()', array(), $e);
}

// VerifyModel
try {
	$oneTimeTokensModel->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'VerifyModel::opschonen()', array(), $e);
}

// Instellingen
try {
	$instellingenRepository->opschonen();
	$lidInstellingenRepository->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', '(Lid)InstellingenModel::instance()->opschonen()', array(), $e);
}

// Corvee herinneringen
try {
	$corveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'CorveeHerinneringenModel::stuurHerinneringen()', array(), $e);
}

// Forum opschonen
try {
	$forumCategorieRepository->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'ForumModel::instance()->opschonen()', array(), $e);
}

passthru('php ../bin/cron/pin_transactie_download.php', $ret);

if ($ret !== 0) {
	$debugLogRepository->log('cron.php', 'pin_transactie_download', [], 'exit '. $ret);
}

$finish = microtime(true) - $start;
if (DEBUG) {
	echo getDateTime() . ' Finished in ' . (int)$finish . " seconds.\r\n";
}
