<?php
require_once 'configuratie.include.php';

if(!$loginlid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
