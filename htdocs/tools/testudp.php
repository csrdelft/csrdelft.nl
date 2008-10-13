<?php
require_once('include.config.php');

if(!$lid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); }

require_once('class.csrgozerbot.php');

$udp = new CsrGozerbot();
$udp->send("hoi", "#csrmeuk");

?>
