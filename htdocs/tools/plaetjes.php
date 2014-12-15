<?php

$file = filter_input(INPUT_GET, 'img', FILTER_SANITIZE_URL);

$alleenLeden = '/(pasfoto|intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';

if (preg_match($alleenLeden, $file) AND ! LoginModel::mag('P_LEDEN_READ')) {
	exit;
}

if (valid_filename($file) AND file_exists($file)) {
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
	readfile($file);
}