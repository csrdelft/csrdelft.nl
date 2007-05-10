<?php

// instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	
// if user has no permission
if (!$lid->hasPermission('P_LOGGED_IN')) {
	echo 'Je bent niet ingelogd!';
	exit();
}

// if 'id' is not valid
if( !isset($_GET['id']) || !is_numeric($_GET['id']) ) {
	echo 'Geen geldige bestands-id opgegeven!';
	exit();
}

// een extra check om het testen makkelijker te maken
file_exists('class.neerladen_.php')
	? require_once('class.neerladen_.php')
	: require_once('class.neerladen.php');
$neerladen = new Neerladen($db);

file_exists('class.neerladencontent_.php')
	? require_once('class.neerladencontent_.php')
	: require_once('class.neerladencontent.php');
$content=new NeerladenContent($db, $neerladen);

$content->view();
?>