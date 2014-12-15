<?php

require_once 'configuratie.include.php';

$img = PICS_PATH . filter_input(INPUT_GET, 'img', FILTER_SANITIZE_STRING);

// voorkom path traversal
if (strpos($img, '..') !== false) {
	http_response_code(403);
	exit;
}

// afschermen voor externen
if (!LoginModel::mag('P_LOGGED_IN')) {
	http_response_code(401);
	exit;
}

if (file_exists($img) AND is_readable($img)) {

	switch (strtolower(substr($img, -4))) {
		case 'jpeg':
		case '.jpg':
			$mime = 'image/jpeg';
			break;
		case '.png':
			$mime = 'image/png';
			break;
		case '.gif':
			$mime = 'image/gif';
			break;
		case '.svg':
			$mime = 'image/svg+xml';
			break;
		case '.bmp':
			$mime = 'image/bmp';
			break;
		case 'tiff':
		case '.tif':
			$mime = 'image/tiff';
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