<?php

# instellingen & rommeltjes
require_once('include.config.php');

require_once('class.courant.php');
$courant = new Courant();
if(!$courant->magToevoegen()){ header('location: '.CSR_ROOT); exit; }

require_once('class.courantbeheercontent.php');
$body = new CourantBeheerContent($courant);

//url waarheen standaard gerefreshed wordt
$courant_url=CSR_ROOT.'intern/courant';


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	if(!isset($_GET['ID'])){ //alleen op de algemene index tonen, niet bij het bewerken oid...
		require_once('class.courantarchiefcontent.php');
		$zijkolom->add(new CourantarchiefContent($courant));
	}
	
	
if($_SERVER['REQUEST_METHOD']=='POST'){
	if($courant->valideerBerichtInvoer()===true){
		$iBerichtID=(int)$_GET['ID'];
		if($iBerichtID==0){
			//nieuw bericht invoeren
			if($courant->addBericht($_POST['titel'], $_POST['categorie'], $_POST['bericht'] )){
				$melding='<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.';
			}else{
				$melding='<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.';
				$courant_url=CSR_ROOT.'/intern/csrmail/?ID=0';
			}
			$body->invokeRefresh($melding, $courant_url);
		}else{
			//bericht bewerken.
			if($courant->bewerkBericht($iBerichtID, $_POST['titel'], $_POST['categorie'], $_POST['bericht'])){
				$melding='<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.';
			}else{
				$melding='<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.';
				$courant_url.='/bewerken/'.$iBerichtID;
			}
			$body->invokeRefresh($courant->getError(), $courant_url);
		}
	}else{
		if(isset($_GET['ID']) AND $_GET['ID']==0){
			//nieuw bericht	
			$body->setMelding($courant->getError());
		}else{
			//bewerken		
			$body->setMelding($courant->getError());
			$body->edit((int)$_GET['ID']);
		}
	}
}else{
	if(isset($_GET['ID'])){
		$iBerichtID=(int)$_GET['ID'];
		if(isset($_GET['verwijder'])){
			if($courant->verwijderBericht($iBerichtID)){
				$body->invokeRefresh('<h3>Uw bericht is verwijderd.</h3>', $courant_url);
			}else{
				$body->invokeRefresh('<h3>Er ging iets mis!</h3>Uw bericht is niet verwijderd. Probeer het a.u.b. nog eens.', $courant_url);
			}
		}
		if(isset($_GET['bewerken'])){
			//bericht bewerken.
			$body->edit($iBerichtID);
		}
	}
}
$pagina=new csrdelft($body);
$pagina->setZijkolom($zijkolom);


$pagina->view();
?>
