<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/abobeheer.php
# -------------------------------------------------------------------
# Zo, abonnementen beheren. Dit kan:
# - Overzicht van Abonnementen
# - Abonnementen toevoegen
# - Abonnementen verwijderen
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

// Deze pagina is alleen voor de leden bedoeld.
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }

require_once 'maaltijden/maaltrack.class.php';
$maaltrack = new MaalTrack();

# actie is abonnement toevoegen of verwijderen
if(isset($_GET['actie'],$_POST['uid'],$_POST['abo'])){
	//valideren
	$error = '';
	if(Lid::exists($_POST['uid']) AND $maaltrack->isValidAbo($_POST['abo'])){
		if($_GET['actie']=='add'){
			if(!$maaltrack->addAbo($_POST['abo'],$_POST['uid'])){
				$error='Abonnement toevoegen mislukt '.$maaltrack->getError();
			}
		}elseif($_GET['actie']=='delete'){
			if(!$maaltrack->delAbo($_POST['abo'],$_POST['uid'])){
				$error='Abonnement verwijderen mislukt '.$maaltrack->getError();
			}
		}else{
			$error='Onbekende actie: '.$_GET['actie'];
		}
	}else{
		//ongeldige invoer
		$error='Ongeldige invoer';
	}
	if($error==''){
		echo 'Abonnementwijziging gelukt';
	}else{
		echo $error;
	}
	exit;
}

require_once 'maaltijden/maaltijdabobeheercontent.class.php';
$content = new MaaltijdabobeheerContent($maaltrack);

$page=new csrdelft($content);
$page->addStylesheet('js/datatables/css/datatables_basic.css');
$page->addScript('datatables/jquery.dataTables.min.js');
$page->addStylesheet('maaltijd.css');
$page->addScript('maaltijdabos.js');

$page->view();

?>
