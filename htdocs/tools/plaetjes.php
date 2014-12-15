<?php

$file = filter_input(INPUT_GET, 'img', FILTER_SANITIZE_URL);

$alleenLeden = '/(pasfoto|intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';

debugprint(PICS_PATH . $file);

if (preg_match($alleenLeden, $file) AND ! LoginModel::mag('P_LEDEN_READ')) {
	http_response_code(403);
	exit;
}

if (valid_filename($file) AND file_exists(PICS_PATH . $file)) {
	/*
	  header('Content-Description: File Transfer');
	  header('Content-Type: application/octet-stream');
	  header('Content-Disposition: attachment; filename=' . basename($file));
	  header('Content-Transfer-Encoding: binary');
	  header('Expires: 0');
	  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	  header('Pragma: public');
	  header('Content-Length: ' . filesize($file));
	  ob_clean();
	  flush();
	 */
	readfile(PICS_PATH . $file);
} else {
	http_response_code(404);
}