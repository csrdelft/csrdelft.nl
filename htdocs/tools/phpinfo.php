<?php
require_once 'configuratie.include.php';

if(!LoginLid::mag('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
