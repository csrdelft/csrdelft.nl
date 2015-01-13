<?php
/*
 * saldodata.php	|	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Serves a json-object with saldi
 */

require_once 'configuratie.include.php';
require_once 'lid/saldi.class.php';

if(!isset($_GET['uid'])){
	echo 'no valid uid';
	exit;
}else{
	if(AccountModel::isValidUid($_GET['uid'])){
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
//niet over de 10 jaar heen.
if($timespan>(10*365)){
	$timespan=(10*365);
}

if(Saldi::magGrafiekZien($uid)){
	echo Saldi::getDatapoints($uid, $timespan);
}

?>
