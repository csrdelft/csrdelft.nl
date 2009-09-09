<?php
/*
 * naamlink.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * geeft een naamlink voor een gegeven uid.
 */

require_once 'include.config.php';

if(isset($_GET['uid'])){
	$string=urldecode($_GET['uid']);
}elseif(isset($_POST['uid'])){
	$string=$_POST['uid'];
}else{
	echo 'Fout in invoer in tools/naamlink.php';
}
if(Lid::isValidUid($string) AND LoginLid::instance()->hasPermission('P_LEDEN_READ')){
	$lid=LidCache::getLid($string);
	if($lid instanceof Lid){
		echo $lid->getNaamLink('civitas', 'link');
	}else{
		echo 'Geen geldig lid';
	}
}else{
	echo 'Fout in invoer in tools/naamlink.php';
}

?>
