<?php

require_once 'configuratie.include.php';

$action = filter_input(INPUT_GET, 'actie', FILTER_SANITIZE_STRING);
switch ($action) {

	case 'su':
		if (!LoginLid::mag('P_ADMIN')) {
			setMelding('Geen su-rechten!', -1);
		} else {
			$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
			LoginLid::instance()->su($uid);
			setMelding('U bekijkt de webstek nu als ' . Lid::getNaamLinkFromUid($_GET['uid']) . '!', 1);
		}
		break;

	case 'endSu':
		if (!LoginLid::instance()->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			LoginLid::instance()->endSu();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		break;
}
if (array_key_exists('HTTP_REFERER', $_SERVER)) {
	$referer = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL);
	if (startsWith($referer, CSR_ROOT)) {
		invokeRefresh($referer);
		exit;
	}
}
invokeRefresh(CSR_ROOT);
