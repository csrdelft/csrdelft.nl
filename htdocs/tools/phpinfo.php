<?php
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

if(!$lid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
