<?php

/**
 * flushcache.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\OrmMemcache;
use CsrDelft\service\CsrfService;

require_once 'configuratie.include.php';

if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {
	CsrfService::preventCsrf();
	if (OrmMemcache::instance()->getCache()->flush()) {
		setMelding('Memcache succesvol geflushed', 2);
	} else {
		setMelding('Memcache flushen mislukt', -1);
	}

	redirect('/tools/memcachestats.php');
}
