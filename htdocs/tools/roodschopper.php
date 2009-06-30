<?php
/*
 * roodschopper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

require_once 'include.config.php';
require_once 'class.roodschopper.php';
require_once 'class.roodschoppercontent.php';

if(!Loginlid::instance()->hasPermission('P_ADMIN,groep:MaalCie,groep:SocCie')){
	header('location: http://csrdelft.nl');
	exit;
}
if(isset($_POST['commissie'], $_POST['bcc'], $_POST['saldogrens'], $_POST['uitsluiten'], $_POST['bericht'])){
	$cie='soccie';
	if($_POST['commissie']=='maalcie'){
		$cie='maalcie';
	}
	$roodschopper=new Roodschopper($cie, (int)$_POST['saldogrens'], $_POST['onderwerp'], $_POST['bericht']);
	$roodschopper->setBcc($_POST['bcc']);
	$roodschopper->setUitgesluiten($_POST['uitsluiten']);
}else{
	$roodschopper=Roodschopper::getDefaults();
}


if(isset($_POST['actie']) AND $_POST['actie']=='simulate'){
	$aantal=$roodschopper->simulate();
	echo 'Er zijn '.$aantal.' leden met een saldo lager dan '.$roodschopper->getSaldogrens();
	exit;
}
	


$pagina=new Csrdelft(new RoodschopperContent($roodschopper));
$pagina->addStylesheet('roodschopper.css');
$pagina->view();

exit;

	
?>
