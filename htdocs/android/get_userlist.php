<?php

require_once 'configuratie.include.php';
require_once 'lid/ledenlijstcontent.class.php';
require_once 'groepen/groep.class.php';

if(!(LoginLid::instance()->hasPermission('P_LOGGED_IN') AND LoginLid::instance()->hasPermission('P_OUDLEDEN_READ'))){
	# geen rechten
	echo 'false';
	exit;
}

$zoeker=new LidZoeker();
$zoeker->parseQuery($_GET);

$leden = array();
$json = "";

foreach($zoeker->getLeden() as $lid) {

	$leden[] = array("id" => $lid->getUid(), "name" => $lid->getNaam());
	//print_r($lid->getUid(). " " . $lid->getNaam());

}

echo '{
    "user": ' . json_encode($leden) . '
}';

?>
