<?php
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

if(!$lid->hasPermission('P_FORUM_MOD')){
	header('location: '.CSR_ROOT);
	exit;
}

phpinfo();

?>
