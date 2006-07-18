<?php

# logout.php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
require_once('include.common.php');

# login-systeem
require_once('class.lid.php');
require_once('class.mysql.php');
session_start();
$db = new MySQL();
$lid = new Lid($db);

$lid->logout();

# url checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url']))
		header("Location: http://csrdelft.nl{$_POST['url']}");

exit;

?>
