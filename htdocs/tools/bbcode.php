<?php

use CsrDelft\service\CsrfService;
use CsrDelft\view\bbcode\CsrBB;

require_once 'configuratie.include.php';
/**
 * bbcode.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Dit scriptje doet niets meer dan server side bbcode parsen op de gegeven string.
 *
 * Wordt gebruikt in de preview van bbcode op het forum
 */

CsrfService::preventCsrf();
if (isset($_POST['data'])) {
	$string = urldecode($_POST['data']);
} elseif (isset($_GET['data'])) {
	$string = $_GET['data'];
} else {
	$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/bbcode.php';
}

$string = trim($string);

echo CsrBB::parse($string);
