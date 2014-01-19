<?php

chdir('../lib/');
require_once 'configuratie.include.php';

if (!LoginLid::instance()->hasPermission('P_LOGGED_IN')) {

	$filter = '/(pasfoto|intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé)/i';
	$request = filter_input(INPUT_GET, 'request', FILTER_SANITIZE_URL);

	if (preg_match($filter, $request)) {
		header('Location: http://csrdelft.nl/');
		exit;
	}
}

echo file_get_contents(PICS_PATH . $request);