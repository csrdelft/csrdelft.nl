<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
require_once('class.commissie.php');

if(isset($_GET['cie'])){
	$commissie = new Commissie($_GET['cie']);
	
	require_once('class.commissiecontent.php');
	$middenvak = new CommissieContent($commissie);
	
	//enkel beheerdingen doen als het met id's gebeurt.
	if(isset($_GET['action']) AND $_GET['cie']==(int)$_GET['cie'] AND $commissie->magBewerken()){ 
		switch($_GET['action']){
			case 'bewerken':
				$middenvak->setAction('edit');
				if(isset($_POST['tekst'], $_POST['stekst'], $_POST['link'])){
					$commissie->setTekst($_POST['tekst']);
					$commissie->setStekst($_POST['stekst']);
					$commissie->setLink($_POST['link']);
					if($commissie->save()){
						$middenvak->invokeRefresh('Opslaan gelukt', CSR_ROOT.'groepen/commissie/'.$commissie->getId());	
					}
				}
			break;
			case 'verwijderen':
				if(isset($_GET['uid'])){
					$commissie->verwijderLid($_GET['uid']);
					$middenvak->invokeRefresh('Lid verwijderd.', CSR_ROOT.'groepen/commissie/'.$commissie->getId());
				}
			break;
			default:
				//alleen nieuwe leden erin gaan stoppen als beide arrays erzijn, en even veel elementen hebben zijn.
				if(isset($_POST['naam'], $_POST['functie']) AND is_array($_POST['naam']) AND is_array($_POST['functie']) AND count($_POST['naam'])==count($_POST['functie'])){
					
					//nieuwe commissieleden erin stoppen.
					for($iTeller=0; $iTeller<count($_POST['naam']); $iTeller++){
						if(preg_match('/^\w{4}$/', $_POST['naam'][$iTeller])){
							$commissie->addLid($_POST['naam'][$iTeller], $_POST['functie'][$iTeller]);
						}
					}
					$middenvak->invokeRefresh('Leden toegevoegd.', CSR_ROOT.'groepen/commissie/'.$commissie->getId());
				}
			break;
		}
	}
	
}else{
	require_once('class.cieoverzichtcontent.php');
	$middenvak = new CieOverzichtContent();
}
## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($middenvak);
$pagina->setZijkolom($zijkolom);
$pagina->view();



?>
