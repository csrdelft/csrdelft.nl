<?php
/**
 * Flush de cache.
 */
use CsrDelft\Orm\Persistence\OrmMemcache;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

if (OrmMemcache::instance()->getCache()->flush()) {
	echo 'Memcache succesvol geflushed';
} else {
	echo 'Memcache flushen mislukt';
	echo error_get_last()["message"];
}

if (delTree(ROUTES_CACHE_PATH)) {
	echo 'Routes succesvol verwijderd';
} else {
	echo 'Routes verwijderen mislukt';
	echo error_get_last()["message"];
}

if (delTree(CONFIG_CACHE_PATH)) {
	echo 'Instelling cache succesvol verwijderd';
} else {
	echo 'Instelling cache verwijderen mislukt';
	echo error_get_last()["message"];
}


