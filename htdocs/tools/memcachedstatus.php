<?php
/*
 * memcachedtest.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */
require_once('include.config.php');

if($lid->hasPermission('P_ADMIN')){
	echo '<h1>MemCached statuspagina';
	$lidCache=LidCache::get_LidCache();
	$stats=$lidCache->getStats();
	pr($stats);
	
	echo 'Dat is dus '.($stats['cmd_get']/$stats['uptime']).' aanroepen van Lid::getNaamLink() per seconde.';
}
?>
