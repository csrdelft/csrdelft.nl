<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	die('false');
}

require_once 'lid/ledenlijstcontent.class.php';
$zoeker = new LidZoeker();
$zoeker->parseQuery($_GET);

$leden = array();
foreach ($zoeker->getLeden() as $profiel) {
	$leden[] = array(
		'id'	 => $profiel->uid,
		'name'	 => $profiel->getNaam()
	);
}
$data = array('user' => $leden);

echo json_encode($data);
exit;
