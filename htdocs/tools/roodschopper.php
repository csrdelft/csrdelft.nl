<?php
/*
 * roodschopper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

require_once 'include.config.php';
require_once 'class.roodschopper.php';
require_once 'class.roodschoppercontent.php';

//Alleen voor admins, maalcie en Soccie. LET OP: SocCie kan nu ook een maalciemail versturen.
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
	$roodschopper->setUitgesloten($_POST['uitsluiten']);
}else{
	$roodschopper=Roodschopper::getDefaults();
}


if(isset($_POST['actie'])){
	switch($_POST['actie']){
		case 'simulate':
			$aantal=$roodschopper->simulate();
			echo 'Er zijn '.$aantal.' leden met een saldo lager dan &euro; '.number_format($roodschopper->getSaldogrens(), 2, ',', '').':<br />';
			echo '<div class="small">'.implode(', ', $roodschopper->getLeden()).'</div>';
			echo '<br />Weet u zeker dat u de roodschopmails aan de bovenstaande personen wilt versturen: <br />';
			echo '<input type="button" value="Ja, verstuur de berichten" onclick="roodschopper(\'verzenden\')" />';
			echo '<input type="button" value="Nee, ik wil nog dingen aanpassen" onclick="restoreRoodschopper()" />';
		break;
		case 'verzenden':
			$roodschopper->doit();
		break;
		
	}			
	exit; //exit voor de XHR-acties.
}

$pagina=new Csrdelft(new RoodschopperContent($roodschopper));
$pagina->addStylesheet('roodschopper.css');
$pagina->addScript('roodschopper.js');
$pagina->view();

	
?>
