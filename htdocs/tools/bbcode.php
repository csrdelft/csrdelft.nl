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

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (isset($_POST['data'])) {
	$string = urldecode($_POST['data']);
} elseif (isset($_GET['data'])) {
	$string = $_GET['data'];
} elseif (isset($input['data'])) {
	$string = urldecode($input['data']);
} else {
	$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/bbcode.php';
}

$string = trim($string);

if (isset($_POST['mail']) || isset($input['mail'])) {
	echo CsrBB::parseMail($string);
} else {
	echo CsrBB::parse($string);
}
