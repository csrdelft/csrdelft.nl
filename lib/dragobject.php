<?php

require_once 'configuratie.include.php';

if (\LoginLid::instance()->hasPermission('P_LEDEN_READ')) {

	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
	$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	
	$_SESSION['dragobject_'. $id] = $coords;
	
	echo print_r($_SESSION);
}

function getDragObjectCoords($id, &$top, &$left) {
	
	if (array_key_exists('dragobject_'. $id, $_SESSION)) {
		
		$top = (int) $_SESSION['dragobject_'. $id]['top'];
		$left = (int) $_SESSION['dragobject_'. $id]['left'];
	}
	
	echo print_r($_SESSION);
}