<?php
# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
//if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.inschrijving.php');
	$inschrijving = new Inschrijving($lid, $db);
	
	//nieuwe aanmelding voor een bestaande inschrijving
	if(isset($_GET['inschrijvingID'], $_GET['aanmelden']) AND 
		!$inschrijving->isVol((int)$_GET['inschrijvingID'])){
		//nieuw persoon aanmelden voor een inschrijving
		if (isset($_GET['partner'],$_GET['eetwens'])) { 
			//gala of andere inschrijving met partnereis
			$inschrijving->meldAanMetPartner((int)$_GET['inschrijvingID'], $lid->getUid(), (int)$_GET['partner'], $_GET['eetwens']);
		} else { 
			//inschrijving zonder partner.
			$inschrijving->meldAan((int)$_GET['inschrijvingID'], $lid->getUid());
		}
		header('location: '.CSR_ROOT.'intern/inschrijving/');
		exit;
	}
	if(isset($_GET['inschrijvingID'], $_GET['afmelden'])){
		//persoon afmelden voor inschrijving
	    $evenement->meldAf((int)$_GET['inschrijvingID'], $lid->getUid);
	}
	
	//nieuwe inschrijving toevoegen
	if(isset($_POST['verzenden']) AND $inschrijving->validateInschrijving() AND $inschrijving->magOrganiseren()){
		$evenement->newInschrijving($_POST['inschrijvingNaam'], $_POST['datum'], $_POST['beschrijving'], $_POST['limiet'], $_POST['partnereis']);
		header('location: '.CSR_ROOT.'intern/inschrijving/');
		exit;
	}	

	require_once('class.inschrijvingcontent.php');
	$midden = new InschrijvingContent($inschrijving);
//} else {
	# geen rechten
	//$midden = new Includer('', 'geentoegang.html');
//}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>