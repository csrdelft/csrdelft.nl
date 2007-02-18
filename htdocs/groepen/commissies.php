<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
require_once('class.commissie.php');
$commissie = new Commissie($db, $lid);

if(isset($_GET['cie'])){
	$commissie->loadCommissie($_GET['cie']);
	//enkel beheerdingen doen als het met id's gebeurt.
	if(preg_match('/^\d+$/', $_GET['cie']) AND $commissie->magBewerken($_GET['cie'])){ 
		$iCieID=(int)$_GET['cie'];
		if(isset($_GET['verwijderen']) AND isset($_GET['uid'])){
			$commissie->verwijderCieLid($iCieID, $_GET['uid']);
			header('location: '.CSR_ROOT.'groepen/commissie/'.$iCieID);
			exit;
		//alleen nieuwe leden erin gaan stoppen als beide arrays erzijn, en even veel elementen hebben zijn.
		}elseif(isset($_POST['naam']) AND isset($_POST['functie']) AND
			is_array($_POST['naam']) AND is_array($_POST['functie']) AND
			count($_POST['naam'])==count($_POST['functie']) ){
			//nieuwe commissieleden erin stoppen.
			for($iTeller=0; $iTeller<count($_POST['naam']); $iTeller++){
				if(preg_match('/^\w{4}$/', $_POST['naam'][$iTeller])){
					$commissie->addCieLid($iCieID, $_POST['naam'][$iTeller], $_POST['functie'][$iTeller]);
				}
			}
			header('location: '.CSR_ROOT.'groepen/commissie/'.$iCieID);
			exit;
		}	
	}
	require_once('class.commissiecontent.php');
	$middenvak = new CommissieContent($commissie, $lid);
} else {
	require_once('class.cieoverzichtcontent.php');
	$middenvak = new CieOverzichtContent($commissie, $lid);
}
## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($middenvak, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();



?>
