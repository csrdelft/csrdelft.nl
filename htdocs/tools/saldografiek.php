<?php
/*
 * saldografiek.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

require_once('include.config.php');
require_once('chart-0.8/chart.php');
require_once('class.saldi.php');


if(isset($_GET['uid']) AND ($lid->isValidUid($_GET['uid']) OR $_GET['uid']=='0000')){
	$uid=$_GET['uid'];
}else{
	$uid=$lid->getUid();
}
require_once('groepen/class.groep.php');
$soccie=new Groep('SocCie');

if($lid->hasPermission('P_ADMIN') OR $soccie->isLid($lid->getUid()) OR $lid->getUid()==$uid){
	if(isset($_GET['timespan']) AND $_GET['timespan']==(int)$_GET['timespan']){
		$timespan=$_GET['timespan'];
	}else{
		$timespan=40;
	}
	$maalcie=new Saldi($uid, 'maalcie', $timespan);
	$soccie=new Saldi($uid, 'soccie', $timespan);
	
	$chart = new chart(500, 200);

	if($uid=='0000'){
		$chart->set_title('Totaal');
	}else{
		$chart->set_title('Saldo voor '.$lid->getNaamLink($uid, 'full', false, false, false));
	}

	$chart->set_x_ticks($soccie->getKeys(), 'date');
	$chart->plot($soccie->getValues(), false, 'blue');
	
	
	$chart->add_legend('SocCie', 'blue');
	//$chart->add_legend('MaalCie', 'red');

	$chart->set_margins(60, 10, 20, 23);
	$chart->set_labels(false, 'Saldo [euro]');
	$chart->stroke();
}
?>
