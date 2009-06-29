<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/beheer.php
# -------------------------------------------------------------------
# Zo, maaltijden beheren. Dit kan:
# - Maaltijden toevoegen
# - Maaltijden bewerken
# - Maaltijden verwijderen
# -------------------------------------------------------------------

require_once 'include.config.php';

if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }

require_once 'maaltijden/class.maaltrack.php';
require_once 'maaltijden/class.maaltijd.php';
$maaltrack = new MaalTrack();


require_once 'maaltijden/class.corveebeheercontent.php';
$beheer = new CorveebeheerContent($maaltrack);


# actie is bewerken, kijken of velden ingevuld zijn
if(isset($_POST['actie'])){
	$maalid=(int)$_POST['maalid'];
	$actie=(int)$_POST['actie'];

	# bestaande maaltijd bewerken
	if($actie == 'bewerk' && (isset($_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))
		&& ($maaltrack->editCorveeMaaltijd($maalid, $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))){
		header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/bewerk/'.$maalid);
		exit;
	} elseif ($actie == 'takenbewerk' && (isset($_POST['kok'], $_POST['afwas'], $_POST['theedoek']))
		&& ($maaltrack->editCorveeMaaltijdTaken($maalid, $_POST['kok'], $_POST['afwas'], $_POST['theedoek']))){
		header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/takenbewerk/'.$maalid);
		exit;
	}
	
	#als we hier terecht komen is het niet goed gegaan, dan maar de foutmelding weergeven...
	$beheer->addError($maaltrack->getError());
	if($actie == 'bewerk')
		$beheer->load($maalid, 'bewerk');
	elseif ($actie == 'takenbewerk')
		$beheer->load($maalid, 'takenbewerk');
}

# bewerken we een maaltijd?
if(isset($_GET['bewerk']) AND $_GET['bewerk']==(int)$_GET['bewerk'] AND $_GET['bewerk']!=0){
	$beheer->load($_GET['bewerk'], 'bewerk');
}
if(isset($_GET['takenbewerk']) AND $_GET['takenbewerk']==(int)$_GET['takenbewerk'] AND $_GET['takenbewerk']!=0){
	$beheer->load($_GET['takenbewerk'], 'takenbewerk');
}

$page=new csrdelft($beheer);
$page->view();

?>
