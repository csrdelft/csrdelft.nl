<?php

require_once 'configuratie.include.php';

//geen maalmod, dan terug naar de maaltijden...
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: http://csrdelft.nl/actueel/maaltijden/'); exit; }

$sStatus='';

require_once 'lid/saldi.class.php';
$sStatus=Saldi::putMaalcieCsv();

class uploader{
	var $sStatus='';
	function uploader($sStatus){
		$this->sStatus=$sStatus;
	}
	function getTitel(){ return "MaalCie-saldi uploaden met een CSV-bestand"; }
	function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('status', $this->sStatus[0]);
		$smarty->display('maaltijdketzer/saldi-updater.tpl');
	}
}
$midden=new uploader($sStatus);


$page=new csrdelft($midden);
$page->view();

?>
