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
if(!Loginlid::instance()->hasPermission('P_ADMIN,groep:MaalCie') AND !Loginlid::instance()->hasPermission('groep:SocCie')){
	header('location: http://csrdelft.nl');
	exit;
}
if(isset($_POST['commissie'], $_POST['bcc'], $_POST['saldogrens'], $_POST['uitsluiten'], $_POST['bericht'], $_POST['from'])){
	$cie='soccie';
	if($_POST['commissie']=='maalcie'){
		$cie='maalcie';
	}
	$roodschopper=new Roodschopper($cie, (int)-abs($_POST['saldogrens']), $_POST['onderwerp'], $_POST['bericht']);
	if(email_like($_POST['bcc'])){
		$roodschopper->setBcc($_POST['bcc']);
	}
	if(email_like($_POST['from'])){
		$roodschopper->setFrom($_POST['from']);
	}
	$roodschopper->setUitgesloten($_POST['uitsluiten']);
}else{
	$roodschopper=Roodschopper::getDefaults();
}


if(isset($_POST['actie'])){
	switch($_POST['actie']){
		case 'simulate':
			if(trim($roodschopper->getBericht())=='' OR trim($roodschopper->getOnderwerp())==''){
				echo '<h1>Formulier is niet compleet</h1>';
				echo '<input type="button" value="Ok, ik pas nog wat aan" onclick="restoreRoodschopper()" />';
				break;
			}
			$aantal=$roodschopper->simulate();
			echo 'Er zijn '.$aantal.' leden met een saldo lager dan &euro; '.number_format($roodschopper->getSaldogrens(), 2, ',', '').':<br />';
			echo '<div class="small">'.implode(', ', $roodschopper->getLeden()).'</div>';
			echo '<br />Weet u zeker dat u de roodschopmails aan de bovenstaande personen wilt versturen: <br />';
			echo '<input type="button" value="Ja, verstuur de berichten" onclick="roodschopper(\'verzenden\')" />';
			echo '<input type="button" value="Eerst nog een voorbeeld" onclick="roodschopper(\'preview\')" />';
			echo '<input type="button" value="Nee, ik wil nog dingen aanpassen" onclick="restoreRoodschopper()" />';
		break;
		case 'preview':
			echo '<input type="button" value="Ja, verstuur de berichten" onclick="roodschopper(\'verzenden\')" />';
			echo '<input type="button" value="Nee, ik wil nog dingen aanpassen" onclick="restoreRoodschopper()" />';
			echo '<hr />';
			$roodschopper->preview();
		break;
		case 'verzenden':
			$roodschopper->doit();
			$_SESSION['melding']='Roodschopmails met succes verzonden.';
		break;
		
	}			
	exit; //exit voor de XHR-acties.
}

$pagina=new Csrdelft(new RoodschopperContent($roodschopper));
$pagina->addStylesheet('roodschopper.css');
$pagina->addScript('roodschopper.js');
$pagina->view();

	
?>
