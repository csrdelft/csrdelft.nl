<?php

/**
 * flushcache.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

if (LoginModel::mag('P_ADMIN')) {

	CsrMemcache::instance()->flush();

	redirect(CSR_ROOT . '/tools/memcachestats.php');
}
