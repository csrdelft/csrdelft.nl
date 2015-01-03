<?php

require_once 'configuratie.include.php';
require_once 'lid/profiel.class.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	echo 'false';
	exit;
}

$lid = LidCache::getLid($_GET['id']);

echo '{
    "user": ' . json_encode(array(
	"id"		 => $lid->getUid(),
	"name"		 => $lid->getNaam(),
	"email"		 => $lid->getEmail(),
	"mobile"	 => $lid->getProperty('mobiel'),
	"phone"		 => $lid->getProperty('telefoon'),
	"address"	 => $lid->getProperty('adres') . "\n" . $lid->getProperty('postcode') . " " . $lid->getProperty('woonplaats')
)) . '
}';
