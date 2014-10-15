<?php

require_once 'configuratie.include.php';
/**
 * ubb.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Dit scriptje doet niets meer dan ubb toepassen op een gegeven string.
 */
if (isset($_POST['data'])) {
	$string = urldecode($_POST['data']);
} elseif (isset($_GET['data'])) {
	$string = $_GET['data'];
} else {
	$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/ubb.php';
}

$string = trim($string);

echo CsrUbb::parse($string);
