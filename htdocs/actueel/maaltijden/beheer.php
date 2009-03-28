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


require_once 'maaltijden/class.maaltijdbeheercontent.php';
$beheer = new MaaltijdbeheerContent($maaltrack);

# verwijderen we een maaltijd?
if(isset($_GET['verwijder']) AND $_GET['verwijder']==(int)$_GET['verwijder'] AND $_GET['verwijder']!=0){
	$maaltrack->removeMaaltijd($_GET['verwijder']);
	header('location: '.CSR_ROOT.'actueel/maaltijden/beheer/');
	exit;
}

# maaltijd opslaan, of nieuwe toevoegen?
if(isset($_POST['maalid'], $_POST['datum'], $_POST['tekst'], $_POST['limiet'], $_POST['abo'],
	$_POST['tp'], $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'] )){
	//datum omzetten naar timestamp.
	$datum=strtotime($_POST['datum']);
	$maalid=(int)$_POST['maalid'];


	# nieuwe maaltijd toevoegen of oude bewerken?
	if($maalid==0){
		if($maaltrack->addMaaltijd($datum, $_POST['tekst'], $_POST['abo'],
				$_POST['tp'], $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['limiet'])){
			header('location: '.CSR_ROOT.'actueel/maaltijden/beheer/');
			exit;
		}
	}else{
		if($maaltrack->editMaaltijd($maalid, $datum, $_POST['tekst'], $_POST['abo'],
				$_POST['tp'], $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'],  $_POST['limiet'])){
			header('location: '.CSR_ROOT.'actueel/maaltijden/beheer/');
			exit;
		}
	}
	#als we hier terecht komen is het niet goed gegaan, dan maar de foutmelding weergeven...
	$beheer->addError($maaltrack->getError());
}


# bewerken we een maaltijd?
if(isset($_GET['bewerk']) AND $_GET['bewerk']==(int)$_GET['bewerk'] AND $_GET['bewerk']!=0){
	$beheer->load($_GET['bewerk']);
}

$page=new csrdelft($beheer);
$page->view();

?>
