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
use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\LogRepository;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\service\corvee\CorveeHerinneringService;

chdir(dirname(__FILE__) . '/../lib/');

/** @var Kernel $kernel */
$kernel = require_once dirname(__DIR__) . '/lib/configuratie.include.php';
$container = $kernel->getContainer();

$start = microtime(true);

$debugLogRepository = $container->get(DebugLogRepository::class);
$logRepository = $container->get(LogRepository::class);
$loginSessionRepository = $container->get(LoginSessionRepository::class);
$oneTimeTokensRepository = $container->get(OneTimeTokensRepository::class);
$instellingenRepository = $container->get(InstellingenRepository::class);
$lidInstellingenRepository = $container->get(LidInstellingenRepository::class);
$corveeHerinneringService = $container->get(CorveeHerinneringService::class);
$forumCategorieRepository = $container->get(ForumCategorieRepository::class);

if (DEBUG) echo "debuglog opschonen\r\n";
try {
	$debugLogRepository->opschonen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', 'debugLogRepository->opschonen', array(), $e);
}

if (DEBUG) echo "Log opschonen\r\n";
try {
	$logRepository->opschonen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', 'logRepository->opschonen', array(), $e);
}

if (DEBUG) echo "LoginSession opschonen\r\n";
try {
	$loginSessionRepository->opschonen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', 'loginSessionsRepository->opschonen', array(), $e);
}

if (DEBUG) echo "One time tokens opschonen\r\n";
try {
	$oneTimeTokensRepository->opschonen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', 'oneTimeTokensRepository->opschonen', array(), $e);
}

if (DEBUG) echo "Instellingen opschonen\r\n";
try {
	$instellingenRepository->opschonen();
	$lidInstellingenRepository->opschonen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', '(Lid)InstellingenRepository->opschonen', array(), $e);
}

if (DEBUG) echo "Corvee herinneringen\r\n";
try {
	$corveeHerinneringService->stuurHerinneringen();
} catch (Exception $e) {
	if (DEBUG) echo $e->getMessage();
	$debugLogRepository->log('cron.php', 'corveeHerinneringenService->stuurHerinneringen', array(), $e);
}

if (DEBUG) echo "Forum opschonen\r\n";
try {
	$forumCategorieRepository->opschonen();
} catch (Exception $e) {
	$debugLogRepository->log('cron.php', 'forumCategorieRepository->opschonen', array(), $e);
}

passthru('php ../bin/cron/pin_transactie_download.php', $ret);

if ($ret !== 0) {
	if (DEBUG) echo $ret;
	$debugLogRepository->log('cron.php', 'pin_transactie_download', [], 'exit '. $ret);
}

$finish = microtime(true) - $start;
if (DEBUG) {
	echo getDateTime() . ' Finished in ' . (int)$finish . " seconds.\r\n";
}
