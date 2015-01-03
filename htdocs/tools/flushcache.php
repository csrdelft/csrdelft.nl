<?php

/**
 * flushcache.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

if (LoginModel::mag('P_ADMIN')) {

	if (CsrMemcache::instance()->flush()) {
		setMelding('Memcache succesvol geflushed', 1);
	} else {
		setMelding('Memcache flushen mislukt', -1);
	}

	redirect(CSR_ROOT . '/tools/memcachestats.php');
}
