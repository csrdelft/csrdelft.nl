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

$cie='soccie';
if(isset($_GET['maalcie'])){
	$cie='maalcie';
}

$timespan=40;
if(isset($_GET['timespan']) AND $_GET['timespan']==(int)$_GET['timespan']){
	$timespan=$_GET['timespan'];
}

if($lid->hasPermission('P_ADMIN,groep:'.$cie) OR $lid->getUid()==$uid){
	$saldi=new Saldi($uid, $cie, $timespan);

	$chart=new chart(500, 200);

	if($uid=='0000'){
		$chart->set_title('Som van de saldi');
	}else{
		$chart->set_title('Saldo voor '.$lid->getNaamLink($uid, 'full', false, false, false));
	}

	$chart->set_x_ticks($saldi->getKeys(), 'date');
	$chart->plot($saldi->getValues(), false, 'blue');

	$chart->add_legend($saldi->getNaam(), 'blue');

	$chart->set_margins(60, 10, 20, 23);
	$chart->set_labels(false, 'Saldo [euro]');
	$chart->stroke();
}
?>
