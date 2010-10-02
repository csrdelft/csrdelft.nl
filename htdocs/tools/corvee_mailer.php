<?php 

require_once 'configuratie.include.php';

if(!$loginlid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

require_once 'maaltijden/maaltrack.class.php';
$maaltrack = new MaalTrack();

$debugMode = (isset($_GET['debug']) ? (int)$_GET['debug'] : null);

$maaltrack->corveeAutoMailer($debugMode);

?>
