<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/corveebeheer.php
# -------------------------------------------------------------------
# Dit geeft alle corvee-activiteiten weer, dus normale maaltijden en 
# corvee-vrijdagen. Aan beiden kan hiermee het volgende aangepast 
# worden:
# - De hoeveelheid punten en het aantal benodigde corveeërs
# - Welke leden zijn ingedeeld voor welke taak
# - Of deze leden punten verdiend hebben voor de taak 
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }

require_once 'maaltijden/maaltrack.class.php';
require_once 'maaltijden/maaltijd.class.php';
$maaltrack = new MaalTrack();


require_once 'maaltijden/corveebeheercontent.class.php';
$beheer = new CorveebeheerContent($maaltrack);


# actie is bewerken, kijken of velden ingevuld zijn
if(isset($_POST['actie']) && isset($_POST['type'])){
	$actie=$_POST['actie'];
	$type=$_POST['type'];
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
		if($actie === 'bewerk' && $type === 'normaal' && (isset($_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))
			&& ($maaltrack->editCorveeMaaltijd($maalid, $_POST['koks'], $_POST['afwassers'], $_POST['theedoeken'], $_POST['punten_kok'], $_POST['punten_afwas'], $_POST['punten_theedoek']))){
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/bewerk/'.$maalid);
			exit;
		} elseif ($actie === 'bewerk' && $type === 'corvee' && (isset($_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'], $_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken']))
			&& ($maaltrack->editSchoonmaakMaaltijd($maalid, $_POST['frituur'], $_POST['afzuigkap'], $_POST['keuken'], $_POST['punten_schoonmaken_frituur'], $_POST['punten_schoonmaken_afzuigkap'], $_POST['punten_schoonmaken_keuken']))){
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/bewerk/'.$maalid);
			exit;
		} elseif ($actie === 'takenbewerk' && $type === 'normaal' && (isset($_POST['kok']) || isset($_POST['afwas']) || isset($_POST['theedoek']))
			&& ($maaltrack->editCorveeMaaltijdTaken($maalid, getOrPost('kok', 'post', array()), getOrPost('afwas', 'post', array()), getOrPost('theedoek', 'post', array())))){									
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/takenbewerk/'.$maalid.'/'.$_POST['filter']);
			exit;
		} elseif ($actie === 'takenbewerk' && $type === 'corvee' && (isset($_POST['frituur']) || isset($_POST['afzuigkap']) || isset($_POST['keuken']))
			&& ($maaltrack->editSchoonmaakMaaltijdTaken($maalid, (isset($_POST['frituur']) ? $_POST['frituur'] : null), (isset($_POST['afzuigkap']) ? $_POST['afzuigkap'] : null), (isset($_POST['keuken']) ? $_POST['keuken'] : null)))) {
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/takenbewerk/'.$maalid.'/'.$_POST['filter']);
			exit;	
		} elseif ($actie === 'puntenbewerk' && $type === 'normaal' 
			&& ($maaltrack->editCorveeMaaltijdPunten($maalid, $punten))) {
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/puntenbewerk/'.$maalid);
			exit;
		} elseif ($actie === 'puntenbewerk' && $type === 'corvee' 
			&& ($maaltrack->editSchoonmaakMaaltijdPunten($maalid, $punten))) {
			header('location: '.CSR_ROOT.'actueel/maaltijden/corveebeheer/puntenbewerk/'.$maalid);
			exit;
		}
		
		#als we hier terecht komen is het niet goed gegaan, dan maar de foutmelding weergeven...
		$beheer->addError($maaltrack->getError());
		if($actie == 'bewerk')
			$beheer->load($maalid, 'bewerk');
		elseif ($actie == 'puntenbewerk')
			$beheer->load($maalid, 'puntenbewerk');
		elseif ($actie == 'takenbewerk')
			$beheer->load($maalid, 'takenbewerk');
	}
}

# bewerken we een maaltijd?
if(isset($_GET['bewerk']) AND $_GET['bewerk']==(int)$_GET['bewerk'] AND $_GET['bewerk']!=0){
	$beheer->load($_GET['bewerk'], 'bewerk');
}
if(isset($_GET['puntenbewerk']) AND $_GET['puntenbewerk']==(int)$_GET['puntenbewerk'] AND $_GET['puntenbewerk']!=0){
	$beheer->load($_GET['puntenbewerk'], 'puntenbewerk');
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
