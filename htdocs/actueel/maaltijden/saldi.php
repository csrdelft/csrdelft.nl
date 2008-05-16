<?php

require_once('include.config.php');

//geen maalmod, dan terug naar de maaltijden...
if(!$lid->hasPermission('P_MAAL_MOD')){ header('location: http://csrdelft.nl/actueel/maaltijden/'); exit; }

$sStatus='';

require_once('class.saldi.php');
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

$zijkolom=new kolom();

$page=new csrdelft($midden);
$page->setZijkolom($zijkolom);
$page->view();

?>
