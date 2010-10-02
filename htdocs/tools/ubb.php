<?php
/*
 * ubb.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Dit scriptje doet niets meer dan ubb toepassen op een gegeven string.
 */

require_once 'configuratie.include.php';

if(isset($_GET['string'])){
	$string=urldecode($_GET['string']);
}elseif(isset($_POST['string'])){
	$string=$_POST['string'];
}else{
	$string='b0rkb0rkb0rk: geen invoer in htdocs/tools/ubb.php';
}

$_SESSION['compose_snapshot']=$string;

$string=trim($string);
echo CsrUBB::instance()->getHTML($string);

?>
