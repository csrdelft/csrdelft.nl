<?php
/*
 * ubb.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Dit scriptje doet niets meer dan ubb toepassen op een gegeven string.
 */

require_once('include.config.php');

if(isset($_GET['string'])){
	$string=urldecode($_GET['string']);
}elseif(isset($_POST['string'])){
	$string=$_POST['string'];
}else{
	$string='b0rkb0rkb0rk: geen invoer in htdocs/tools/ubb.php';
}

$string=trim($string);
echo CsrUBB::instance()->getHTML($string);

?>
