<?php


# instellingen & rommeltjes
require_once 'include.config.php';

// if user has no permission
if (!$loginlid->hasPermission('P_LOGGED_IN')) {
	echo 'Je bent niet ingelogd!';
	exit();
}

// if 'id' is not valid
if( !isset($_GET['id']) || !is_numeric($_GET['id']) ) {
	echo 'Geen geldige bestands-id opgegeven!';
	exit();
}

require_once('class.neerladen.php');
$neerladen = new Neerladen();

require_once('class.neerladencontent.php');
$content=new NeerladenContent($neerladen);

$content->view();
?>
