<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijdlijst.php
# -------------------------------------------------------------------


require_once 'configuratie.include.php';

$maalid=(int)$_GET['maalid'];

if($maalid==0){
	SimpleHTML::invokeRefresh('Geen maaltijd-id meegegeven', 'actueel/maaltijden/');
}

# MaaltijdenSysteem
require_once 'maaltijden/class.maaltrack.class.php';
require_once 'maaltijden/class.maaltijd.class.php';
$maaltrack = new MaalTrack();           

# bestaat de maaltijd?
if (!$maaltrack->isMaaltijd($maalid)){
	SimpleHTML::invokeRefresh('Maaltijd bestaat niet!', '/actueel/maaltijden/');
}

$maaltijd = new Maaltijd($maalid);

# Moet deze maaltijd gesloten worden?
if(($loginlid->hasPermission('P_MAAL_MOD') OR opConfide()) AND isset($_GET['sluit']) and $_GET['sluit'] == 1) {
	$maaltijd->sluit();
	SimpleHTML::invokeRefresh(null, '/actueel/maaltijden/lijst/'.$maalid);
	exit;
}

require_once 'maaltijden/maaltijdlijstcontent.class.php';
$page = new MaaltijdLijstContent($maaltijd);

# Moeten we de fiscaal-lijst weergeven?
if(isset($_GET['fiscaal']) && $_GET['fiscaal']==1){
	$page->setFiscaal(true);
}
if($loginlid->hasPermission('P_MAAL_MOD') OR opConfide() OR $maaltijd->isTp()){
	$page->view();
}else{
	SimpleHTML::invokeRefresh('U mag de maaltijdlijst niet bekijken.', '/actueel/maaltijden/');
}


?>
