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
if(isset($_POST['actie']) && isset($_POST['type'])){
	$actie=(int)$_POST['actie'];
	$type=(int)$_POST['type'];
	$maalid=(int)$_POST['maalid'];
	if(isset($_POST['punten']))
		$punten = $_POST['punten'];
	else
		$punten = array();
	if(isset($_POST['datum']))
		$datum=strtotime($_POST['datum']);
		
	# nieuwe maaltijd toevoegen of oude bewerken?
	if($actie == 'toevoegen' && $type=='corvee' && isset($datum, $_POST['tekst'], $_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'],$_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken'])
	   	&& $maaltrack->addSchoonmaakMaaltijd($datum, $_POST['tekst'], 
		$_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'],
		$_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken']
		)){
		header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/');
		exit;
	}else{	
		# bestaande maaltijd bewerken
		if($actie == 'bewerk' && $type == 'normaal' && (isset($_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))
			&& ($maaltrack->editCorveeMaaltijd($maalid, $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))){
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/bewerk/'.$maalid);
			exit;
		} elseif ($actie == 'bewerk' && $type == 'corvee' && (isset($_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'], $_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken']))
			&& ($maaltrack->editSchoonmaakMaaltijd($maalid, $_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'], $_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken']))){
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/bewerk/'.$maalid);
			exit;
		} elseif ($actie == 'takenbewerk' && $type == 'normaal' && (isset($_POST['kok'], $_POST['afwas'], $_POST['theedoek']))
			&& ($maaltrack->editCorveeMaaltijdTaken($maalid, $_POST['kok'], $_POST['afwas'], $_POST['theedoek'], $punten))){									
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/takenbewerk/'.$maalid.'/'.$_POST['filter']);
			exit;
		} elseif ($actie == 'takenbewerk' && $type == 'corvee' && (isset($_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'])
			&& ($maaltrack->editSchoonmaakMaaltijdTaken($maalid, $_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'], $punten)))){
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/takenbewerk/'.$maalid.'/'.$_POST['filter']);
			exit;	
		}
		
		#als we hier terecht komen is het niet goed gegaan, dan maar de foutmelding weergeven...
		$beheer->addError($maaltrack->getError());
		if($actie == 'bewerk')
			$beheer->load($maalid, 'bewerk');
		elseif ($actie == 'takenbewerk')
			$beheer->load($maalid, 'takenbewerk');
	}
}

# bewerken we een maaltijd?
if(isset($_GET['bewerk']) AND $_GET['bewerk']==(int)$_GET['bewerk'] AND $_GET['bewerk']!=0){
	$beheer->load($_GET['bewerk'], 'bewerk');
}
if(isset($_GET['takenbewerk']) AND $_GET['takenbewerk']==(int)$_GET['takenbewerk'] AND $_GET['takenbewerk']!=0){
	if(!isset($_GET['filter'])){
		$beheer->load($_GET['takenbewerk'], 'takenbewerk');
	}
	else {
		$beheer->load($_GET['takenbewerk'], 'takenbewerk', $_GET['filter']);
	}
}

$page=new csrdelft($beheer);
$page->view();

?>
