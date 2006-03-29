<?php

# login.php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
require_once('include.common.php');

# login-systeem
require_once('class.lid.php');
require_once('class.mysql.php');
session_start();
$db = new MySQL();
$lid = new Lid($db);

# ok_url beetje checken, zodat er geen zooi in geinsert wordt.
if ($lid->login($_POST['user'],$_POST['pass']) and preg_match("/[^ \"\n\r\t<]*?/", $_POST['ok_url'])) {
	header("Location: {$_POST['ok_url']}");
	exit;
}

$_SESSION['auth_error'] = "Ongeldige gebruiker of wachtwoord";
# beetje checken, zodat er geen zooi in geinsert wordt.
if (preg_match("/[^ \"\n\r\t<]*?/", $_POST['not_ok_url'])) {
	header("Location: {$_POST['not_ok_url']}");
	exit;
}

header("Location: /");
exit;

?>
