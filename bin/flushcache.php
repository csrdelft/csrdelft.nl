<?php
/**
 * Flush de cache.
 */

use CsrDelft\common\ContainerFacade;
use CsrDelft\Orm\Persistence\OrmMemcache;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

if (ContainerFacade::getContainer()->get(OrmMemcache::class)->getCache()->flush()) {
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


