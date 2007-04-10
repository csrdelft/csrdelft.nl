<?php
# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.loungeactiviteit.php');
	$loungeactiviteit = new Loungeactiviteit($lid, $db);
	
	//nieuwe aanmelding voor een bestaande sjaarsactie
	if(isset($_GET['actieID'], $_GET['aanmelden']) AND !$loungeactiviteit->isVol((int)$_GET['actieID'])){
		//nieuw persoon aanmelden voor een actie
		$loungeactiviteit->meldAan((int)$_GET['actieID'], $lid->getUid());
		header('location: '.CSR_ROOT.'intern/mekellounge/');
		exit;
	}
	//nieuwe sjaarsactie aanmelden
	if(isset($_POST['verzenden']) AND $loungeactiviteit->validateLoungeactiviteit() AND $loungeactiviteit->_lid->getUid()=='0622' OR $loungeactiviteit->_lid->getUid()=='0308'){
		$loungeactiviteit->newLoungeactiviteit($_POST['actieNaam'], $_POST['beschrijving'], $_POST['limiet']);
		header('location: '.CSR_ROOT.'intern/mekellounge/');
		exit;
	}
		
	require_once('class.loungeactiviteitcontent.php');
	$midden = new LoungeactiviteitContent($loungeactiviteit);
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
