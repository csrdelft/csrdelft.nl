<?php
require_once 'configuratie.include.php';

$return  = Array();
if(LoginLid::instance()->hasPermission('P_LEDEN_READ') and ISSET($_GET['afmelden'])){
	require_once 'maaltijden/maaltrack.class.php';
	require_once 'maaltijden/maaltijd.class.php';
	$maaltrack = new Maaltrack();
	$uid = LoginLid::instance()->getUid();
	$return[]= $maaltrack->afmelden($_GET['afmelden'], $uid) ;
	
}else if(LoginLid::instance()->hasPermission('P_LEDEN_READ') and ISSET($_GET['aanmelden'])){
		require_once 'maaltijden/maaltrack.class.php';
		require_once 'maaltijden/maaltijd.class.php';
		$maaltrack = new Maaltrack();
		$uid = LoginLid::instance()->getUid();
		$return[]= $maaltrack->aanmelden($_GET['aanmelden'], $uid);

}else{

	if(LoginLid::instance()->hasPermission('P_LEDEN_READ') and $_GET['next10maal']='true'){
		require_once 'maaltijden/maaltrack.class.php';
		require_once 'maaltijden/maaltijd.class.php';
		$maaltrack = new Maaltrack();
		$maaltijden= $maaltrack->getMaaltijden();
		$res = Array();
		foreach($maaltijden as $maaltijd){
			$res[]=$maaltijd;
		}
		$return["maaltijden"] = $res;
	}

	if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
		$return["login"] = "true";
	
	}else{
		$return["login"] = "false";
}
}



echo json_encode($return);

?>