<?php

require_once 'configuratie.include.php';

$data = false;
if (isset($_POST['user']) and isset($_POST['pass'])) {
	$data = LoginModel::instance()->login(strval($_POST['user']), strval($_POST['pass']));
}

echo json_encode($data);
exit;
