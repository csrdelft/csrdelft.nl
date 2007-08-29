<?php
require_once('include.config.php');

if(!$lid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
