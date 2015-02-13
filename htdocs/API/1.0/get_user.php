<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	die('false');
}

$profiel = ProfielModel::get($_GET['id']);

if ($profiel) {
	$data = array(
		'user' => array(
			'id'		 => $profiel->uid,
			'name'		 => $profiel->getNaam(),
			'email'		 => $profiel->getPrimaryEmail(),
			'mobile'	 => $profiel->mobiel,
			'phone'		 => $profiel->telefoon,
			'address'	 => $profiel->getFormattedAddress()
		)
	);
} else {
	$data = array();
}

echo json_encode($data);
exit;
