<?php

require_once 'configuratie.include.php';

$action = filter_input(INPUT_GET, 'actie', FILTER_SANITIZE_STRING);
switch ($action) {

	case 'su':
		if (!$loginlid->hasPermission('P_ADMIN')) {
			setMelding('Geen su-rechten!', -1);
		} else {
			$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
			$loginlid->su($uid);
			setMelding('U bekijkt de webstek nu als ' . Lid::getNaamLinkFromUid($_GET['uid']) . '!', 1);
		}
		invokeRefresh();
		break;

	case 'endSu':
		if (!$loginlid->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$loginlid->endSu();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		invokeRefresh();
		break;
}
