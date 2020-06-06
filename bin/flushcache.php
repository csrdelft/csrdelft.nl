<?php
/**
 * Flush de cache.
 */

use CsrDelft\common\ContainerFacade;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

$cache = ContainerFacade::getContainer()->get('stek.cache.memcache');

if ($cache->flush()) {
	echo 'Memcache succesvol geflushed' . PHP_EOL;
} else {
	echo 'Memcache flushen mislukt' . PHP_EOL;
	echo error_get_last()["message"];
}

if (delTree(CONFIG_CACHE_PATH)) {
	echo 'Instelling cache succesvol verwijderd' . PHP_EOL;
} else {
	echo 'Instelling cache verwijderen mislukt' . PHP_EOL;
	echo error_get_last()["message"];
}


