<?php
/*
 * saldodata.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * serves json with saldi
 */

require_once 'include.config.php';
require_once 'lid/class.saldi.php';


if(!isset($_GET['uid'])){
	echo 'no valid uid';
	exit;
}else{
	if(Lid::isValidUid($_GET['uid'])){
		$uid=$_GET['uid'];
	}else{
		echo 'no valid uid';
		exit;
	}
}

$timespan=40;
if(isset($_GET['timespan'])){
	$timespan=(int)$_GET['timespan'];
}
if($timespan>(10*365)){
	$timespan=(10*365);
}


if(Saldi::magGrafiekZien($loginlid->getUid())){
	echo Saldi::getDatapoints($uid, $timespan);
}
