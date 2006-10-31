<?php


# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	### Pagina-onderdelen ###

# Moeten er acties uitgevoerd worden?
if (isset($_POST['maalid'])) $maalid = $_POST['maalid'];
elseif (isset($_GET['maalid'])) $maalid = $_GET['maalid'];
else $maalid = '';

# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
# te kunnen laden in plaats van de profiel pagina omdat er geen
# toegang wordt verleend voor de actie die gevraagd wordt.
$error = 0;

# controleren of we wel mogen doen wat er gevraagd wordt...
if ( $maalid == '' or ( !$lid->hasPermission('P_MAAL_MOD') and !opConfide() )	) $error = 1;

# Pagina maken
if ($error == 0  or $error == 2) {

	# MaaltijdenSysteem
	require_once('class.maaltrack.php');
	require_once('class.maaltijd.php');
	$maaltrack = new MaalTrack($lid, $db);

	# bestaat de maaltijd?
	if (!$maaltrack->isMaaltijd($maalid)) die("Maaltijd bestaat niet!");
	# zo ja, maak object en pagina
	$maaltijd = new Maaltijd($maalid, $lid, $db);

	# Moet deze maaltijd gesloten worden?
	if (isset($_GET['sluit']) and $_GET['sluit'] == 1) {
		$maaltijd->sluit();	
		header("Location: {$_SERVER['PHP_SELF']}?maalid={$maalid}");
		exit;
	}

	require_once('class.maaltijdlijstpage.php');
	$page = new MaaltijdLijstPage($lid, $maaltijd);
	$page->view();

} else {
	die("Kekschooier! Dat mag niet!");
}


?>
