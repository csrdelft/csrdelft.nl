<?php

require_once 'configuratie.include.php';
require_once 'lid/ledenlijstcontent.class.php';
require_once 'model/entity/groepen/OldGroep.class.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	echo 'false';
	exit;
}

$zoeker = new LidZoeker();
$zoeker->parseQuery($_GET);

$leden = array();
$json = '';

foreach ($zoeker->getLeden() as $profiel) {
	$leden[] = array("id" => $profiel->uid, "name" => $profiel->getNaam());
}

echo '{
    "user": ' . json_encode($leden) . '
}';
