<?php

use CsrDelft\model\security\LoginModel;
use CsrDelft\service\CsrfService;

require_once 'configuratie.include.php';

if (LoginModel::mag(P_LOGGED_IN)) {
	CsrfService::preventCsrf();
	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
	$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

	$_SESSION['dragobject'][$id] = $coords;
}
