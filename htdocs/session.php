<?php

require_once 'configuratie.include.php';

if (\LoginLid::instance()->hasPermission('P_LEDEN_READ')) {

	$set = filter_input(INPUT_POST, 'set', FILTER_SANITIZE_STRING);
	$array = filter_input(INPUT_POST, 'array', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	$_SESSION[$set] = $array;
}
