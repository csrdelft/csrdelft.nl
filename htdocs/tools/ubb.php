<?php
/*
 * ubb.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Dit scriptje doet niets meer dan ubb toepassen op een gegeven string.
 */

require_once('include.config.php');

if(isset($_GET['string'])){
	$string=trim(urldecode($_GET['string']));
	$ubb=new CsrUBB();
	echo $ubb->getHTML($string);
}else{
	return 'b0rkb0rkb0rk';
}
?>
