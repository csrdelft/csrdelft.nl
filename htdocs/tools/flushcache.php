<?php

/**
 * flushcache.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
require_once 'configuratie.include.php';

if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {

	if (CsrMemcache::instance()->flush()) {
		setMelding('Memcache succesvol geflushed', 1);
	} else {
		setMelding('Memcache flushen mislukt', -1);
	}

	redirect('/tools/memcachestats.php');
}