<?php
/**
 * cron.monthly.php
 *
 * @author Jorai Rijsdijk <jorairijsdijk@gmail.com>
 *
 * Entry point voor uitvoeren van maandelijkse CRON-jobs.
 *
 * 'geinstalleerd' met:
 * crontab -e
 * 0 2 1 * * /usr/www/csrdelft.nl/bin/cron.monthly.php >> /srv/www/csrdelft.nl/data/log/cron.log 2>&1
 * test door ./cron.monthly.php te typen
 */

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\DebugLogRepository;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

$start = microtime(true);

try {
    passthru('php ../bin/cron/sponsor_affiliates_download.php');
} catch (Exception $e) {
    ContainerFacade::getContainer()->get(DebugLogRepository::class)->log('cron.php', 'php sponsor_affiliates_download.php', array(), $e);
}

$finish = microtime(true) - $start;
if (DEBUG) {
    echo getDateTime() . ' Finished in ' . (int)$finish . " seconds.\r\n";
}

