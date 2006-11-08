<?php
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.sjaarsactie.php');
	$sjaarsactie = new Sjaarsactie($lid, $db);
	
	//nieuwe aanmelding voor een bestaande sjaarsactie
	if(isset($_GET['actieID'], $_GET['aanmelden']) AND 
		$sjaarsactie->isSjaars() AND !$sjaarsactie->isVol((int)$_GET['actieID'])){
		//nieuw persoon aanmelden voor een actie
		$sjaarsactie->meldAan((int)$_GET['actieID'], $lid->getUid());
		header('location: '.CSR_ROOT.'intern/sjaarsacties/');
		exit;
	}
	//nieuwe sjaarsactie aanmelden
	if(isset($_POST['verzenden']) AND $sjaarsactie->validateSjaarsactie()){
		$sjaarsactie->newSjaarsactie($_POST['actieNaam'], $_POST['beschrijving'], $_POST['limiet']);
		header('location: '.CSR_ROOT.'intern/sjaarsacties/');
		exit;
	}
		
	require_once('class.sjaarsactiecontent.php');
	$midden = new SjaarsactieContent($sjaarsactie);
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
