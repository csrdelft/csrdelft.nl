<?php

require_once 'configuratie.include.php';
require_once 'lid/profiel.class.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	echo 'false';
	exit;
}

$profiel = ProfielModel::get($_GET['id']);

echo '{
    "user": ' . json_encode(array(
	"id"		 => $profiel->uid,
	"name"		 => $profiel->getNaam(),
	"email"		 => $profiel->getPrimaryEmail(),
	"mobile"	 => $profiel->mobiel,
	"phone"		 => $profiel->telefoon,
	"address"	 => $profiel->getFormattedAddress()
)) . '
}';
