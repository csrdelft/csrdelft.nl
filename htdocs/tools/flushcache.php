<?php
/*
 * memcachedtest.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once('include.config.php');

if($loginlid->hasPermission('P_ADMIN')){
	echo '<h1>MemCached flushen</h1>';
	Memcached::instance()->flush();
	
}
?>
