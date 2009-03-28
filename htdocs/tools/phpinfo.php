<?php
require_once('include.config.php');

if(!$loginlid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
