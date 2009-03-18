<?php
/*
 * memcachedtest.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once('include.config.php');

if($lid->hasPermission('P_ADMIN')){
	echo '<h1>MemCached statuspagina</h1>';
	pr(Memcached::instance());
	pr($stats);

	echo 'Dat is dus '.($stats['cmd_get']/$stats['uptime']).' aanroepen van Lid::getNaamLink() per seconde.';
}
?>
