<?php


# instellingen & rommeltjes
require_once('include.config.php');
	### Pagina-onderdelen ###

# Moeten er acties uitgevoerd worden?
$maalid=getOrPost('maalid');

# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
# te kunnen laden in plaats van de profiel pagina omdat er geen
# toegang wordt verleend voor de actie die gevraagd wordt.
$error = 0;

# controleren of we wel mogen doen wat er gevraagd wordt...
if($maalid == '' or (!$lid->hasPermission('P_MAAL_MOD') and !opConfide())) $error = 1;

# Pagina maken
if ($error == 0  or $error == 2) {

	# MaaltijdenSysteem
	require_once('maaltijden/class.maaltrack.php');
	require_once('maaltijden/class.maaltijd.php');
	$maaltrack = new MaalTrack($lid, $db);

	# bestaat de maaltijd?
	if (!$maaltrack->isMaaltijd($maalid)) die("Maaltijd bestaat niet!");
	# zo ja, maak object en pagina
	$maaltijd = new Maaltijd($maalid, $lid, $db);

	# Moet deze maaltijd gesloten worden?
	if (isset($_GET['sluit']) and $_GET['sluit'] == 1) {
		$maaltijd->sluit();	
		header('Location: '.CSR_ROOT.'actueel/maaltijden/lijst/'.$maalid);
		exit;
	}

	require_once('maaltijden/class.maaltijdlijstcontent.php');
	$page = new MaaltijdLijstContent($maaltijd);
	
	# Moeten we de fiscaal-lijst weergeven?
	if(isset($_GET['fiscaal']) && $_GET['fiscaal']==1){
		$page->setFiscaal(true);
	}
	
	$page->view();

} else {
	die("HEE, Kekschooier! Dat mag niet!");
}


?>
