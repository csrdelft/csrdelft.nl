<?php

require_once 'configuratie.include.php';

if (\LoginLid::instance()->hasPermission('P_LEDEN_READ')) {

	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
	$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	
	if (!array_key_exists('dragobject', $_SESSION)) {
		$_SESSION['dragobject'] = array();
	}
	$_SESSION['dragobject'][$id] = $coords;
	
	echo print_r($_SESSION['dragobject']);
}

function getDragObjectCoords($id, &$top, &$left) {
	
	if (array_key_exists('dragobject', $_SESSION) && array_key_exists($id, $_SESSION['dragobject'])) {
		
		$top = (int) $_SESSION['dragobject'][$id]['top'];
		$left = (int) $_SESSION['dragobject'][$id]['left'];
	}
	
	echo print_r($_SESSION['dragobject']);
}