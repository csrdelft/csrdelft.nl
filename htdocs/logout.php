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

if (isset($_POST['ok_url'])) $ok_url = $_POST['ok_url'];
else $ok_url = CSR_ROOT;

# beetje checken, zodat er geen zooi in geinsert wordt.
if (preg_match("/[^ \"\n\r\t<]*?/", $ok_url))
	header("Location: {$ok_url}");


?>
