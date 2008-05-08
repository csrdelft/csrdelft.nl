<?php
/*
 * memcachedtest.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */
require_once('include.config.php');

if($lid->hasPermission('P_ADMIN')){
	$lidCache=LidCache::get_LidCache();
	pr($lidCache->getStats());
}
?>
