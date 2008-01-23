<?php
# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
$body = new kolom();
	
$verhaal = new Includer('', 'werkgroepen.html');
$body->add($verhaal);
	
	
	require_once('class.werkgroep.php');
	$werkgroep = new Werkgroep();
	
	//nieuwe aanmelding voor een bestaande sjaarsactie
	if(isset($_GET['actieID'], $_GET['aanmelden']) AND !$werkgroep->isVol((int)$_GET['actieID']) && $werkgroep->_lid->hasPermission('P_LEDEN_READ')){
		//nieuw persoon aanmelden voor een actie
		$werkgroep->meldAan((int)$_GET['actieID'], $lid->getUid());
		header('location: '.CSR_ROOT.'groepen/werkgroepen.php');
		exit;
	}
	//nieuwe sjaarsactie aanmelden
	if(isset($_POST['verzenden']) AND $werkgroep->validateWerkgroep() AND ($lid->getUid()=='0622')){
		$werkgroep->newWerkgroep($_POST['actieNaam'], $_POST['beschrijving'], $_POST['limiet']);
		header('location: '.CSR_ROOT.'groepen/werkgroepen.php');
		exit;
	}
		
	require_once('class.werkgroepcontent.php');
	$werkgroepen=new WerkgroepContent($werkgroep);
	$body->add($werkgroepen);


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($body, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('forum.css');
$pagina->view();

?>
