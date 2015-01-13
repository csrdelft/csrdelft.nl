<?php

require_once 'configuratie.include.php';

$img = PICS_PATH . filter_input(INPUT_GET, 'img', FILTER_SANITIZE_STRING);

// voorkom path traversal
if (strpos($img, '..') !== false) {
	http_response_code(403);
	die('<h1>403 Forbidden</h1>');
}

// afschermen voor externen
if (!LoginModel::mag('P_LOGGED_IN')) {
	http_response_code(401);
	die('<h1>401 Unauthorized</h1>');
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
			die('<h1>415 Unsupported Media Type</h1>');
	}

	header('Content-type: ' . $mime);
	header('Content-length: ' . filesize($img));
	readfile($img);
} else {
	http_response_code(404);
	die('<h1>404 Not Found</h1>');
}