<?php

/**
 * syncldap.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */

use CsrDelft\common\LDAP;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';
require_once 'LDAP.php';

if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {

	$ldap = new LDAP();
	$model = ProfielModel::instance();

	foreach ($model->find() as $profiel) {
		$model->save_ldap($profiel, $ldap);
	}

	$ldap->disconnect();

	echo 'done';
}
