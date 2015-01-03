<?php

/**
 * memcachestats.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

if (LoginModel::mag('P_ADMIN')) {

	echo getMelding();

	echo '<h1>MemCache statistieken</h1>';

	debugprint(CsrMemcache::instance()->getStats());
}
