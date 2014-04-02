<?php
require_once 'configuratie.include.php';

if(!LoginLid::instance()->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
