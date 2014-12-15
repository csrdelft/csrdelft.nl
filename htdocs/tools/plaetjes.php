<?php

$file = filter_input(INPUT_GET, 'img', FILTER_SANITIZE_URL);

$alleenLeden = '/(pasfoto|intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';

if (preg_match($alleenLeden, $file) AND ! LoginModel::mag('P_LEDEN_READ')) {
	http_response_code(401);
	exit;
}

if (valid_filename($file) AND file_exists(PICS_PATH . $file) AND is_readable(PICS_PATH . $file)) {

	switch (substr($file, -4)) {
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
	header('Content-length: ' . filesize(PICS_PATH . $file));
	readfile(PICS_PATH . $file);
} else {
	http_response_code(404);
}