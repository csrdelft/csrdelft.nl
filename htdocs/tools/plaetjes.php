<?php

require_once 'configuratie.include.php';

$img = PICS_PATH . filter_input(INPUT_GET, 'img', FILTER_SANITIZE_STRING);

// voorkom path traversal
if (strpos($img, '..') !== false) {
	http_response_code(403);
	exit;
}

$alleenLeden = '/(pasfoto|intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';

if (preg_match($alleenLeden, $img) AND ! LoginModel::mag('P_LEDEN_READ')) {
	http_response_code(401);
	exit;
}

if (file_exists($img) AND is_readable($img)) {

	switch (strtolower(substr($img, -4))) {
		case 'jpeg':
		case '.jpg':
			$mime = 'image/jpeg';
			break;
		case '.gif':
			$mime = 'image/gif';
			break;
		case '.png':
			$mime = 'image/png';
			break;
		default:
			http_response_code(415);
			exit;
	}

	header('Content-type: ' . $mime);
	header('Content-length: ' . filesize($img));
	readfile($img);
} else {
	http_response_code(404);
}