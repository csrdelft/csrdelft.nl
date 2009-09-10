<?php
/*
 * naamlink.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * geeft een naamlink voor een gegeven uid.
 */

require_once 'include.config.php';


if(!LoginLid::instance()->hasPermission('P_LEDEN_READ')){
	echo 'Niet voldoende rechten';
	exit;
}

if(isset($_GET['uid'])){
	$string=urldecode($_GET['uid']);
}elseif(isset($_POST['uid'])){
	$string=$_POST['uid'];
}else{
	echo 'Fout in invoer in tools/naamlink.php';
}

function uid2naam($uid){
	$lid=LidCache::getLid($uid);
	if($lid instanceof Lid){
		return $lid->getNaamLink('civitas', 'link');
	}else{
		return 'Geen geldig lid';
	}
}

if(Lid::isValidUid($string)){
	echo uid2naam($string);
}else{
	$uids=explode(',', $string);
	foreach($uids as $uid){
		echo uid2naam($uid);
	}
}

?>
