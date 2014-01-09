<?php

require_once 'configuratie.include.php';

if (\LoginLid::instance()->hasPermission('P_LEDEN_READ')) {

	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
	$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	
	$_SESSION['dragobject'][$id] = $coords;
}
