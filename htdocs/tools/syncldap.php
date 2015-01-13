<?php

/**
 * syncldap.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
require_once 'configuratie.include.php';
require_once 'ldap.class.php';

if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {

	$ldap = new LDAP();
	$model = ProfielModel::instance();

	foreach ($model->find() as $profiel) {
		$model->save_ldap($profiel, $ldap);
	}

	$ldap->disconnect();

	echo 'done';
}