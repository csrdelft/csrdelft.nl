<?php 

require_once 'include.config.php';

if(!$loginlid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}

require_once 'maaltijden/class.maaltrack.php';
$maaltrack = new MaalTrack();

$maaltrack->corveeAutoMailer();

?>
