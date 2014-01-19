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
chdir('../images/');
$path = $request;
$ext = pathinfo($path, PATHINFO_EXTENSION);

header("Content-Type: image/" . $ext);
header("Content-Length: " . filesize($path));
header("Cache-Control: maxage=21000"); // 6 dagen
header("Expires: " . gmdate('D, d M Y H:i:s', (time() + 21000)) . ' GMT');

$fp = fopen($path, 'rb'); // open in a binary mode
fpassthru($fp);
