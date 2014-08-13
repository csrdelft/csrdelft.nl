<?php

/**
 * memcachestats.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

if (LoginModel::mag('P_ADMIN')) {

	echo '<h1>MemCache statistieken</h1>';

	$stats = CsrMemcache::instance()->getStats();

	echo '<pre>' . print_r($stats, true) . '</pre>';
	echo 'Dat is dus ' . ($stats['cmd_get'] / $stats['uptime']) . ' aanroepen van Lid::getNaamLink() per seconde.';
}
