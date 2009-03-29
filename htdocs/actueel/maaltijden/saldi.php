<?php

require_once('include.config.php');

//geen maalmod, dan terug naar de maaltijden...
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: http://csrdelft.nl/actueel/maaltijden/'); exit; }

$sStatus='';

require_once 'lid/class.saldi.php';
$sStatus=Saldi::putMaalcieCsv();

class uploader{
	var $sStatus='';
	function uploader($sStatus){
		$this->sStatus=$sStatus;
	}
	function getTitel(){ return "MaalCie-saldi uploaden met een CSV-bestand"; }
	function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('status', $this->sStatus);
		$smarty->display('maaltijdketzer/saldi-updater.tpl');
	}
}
$midden=new uploader($sStatus);


$page=new csrdelft($midden);
$page->view();

?>
