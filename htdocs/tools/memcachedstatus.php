<?php
/*
 * memcachedtest.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'configuratie.include.php';

if($loginlid->hasPermission('P_ADMIN')){
	echo '<h1>MemCached statuspagina</h1>';
	$stats=Memcached::instance()->getStats();
	pr($stats);
	echo 'Dat is dus '.($stats['cmd_get']/$stats['uptime']).' aanroepen van Lid::getNaamLink() per seconde.';
}
?>
