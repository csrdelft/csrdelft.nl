<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijdlijst.php
# -------------------------------------------------------------------


require_once 'configuratie.include.php';

$maalid=(int)$_GET['maalid'];

if($maalid==0){
	SimpleHTML::invokeRefresh('actueel/maaltijden/', 'Geen maaltijd-id meegegeven');
}

# MaaltijdenSysteem
require_once 'maaltijden/maaltrack.class.php';
require_once 'maaltijden/maaltijd.class.php';
$maaltrack = new MaalTrack();           

# bestaat de maaltijd?
if (!$maaltrack->isMaaltijd($maalid)){
	SimpleHTML::invokeRefresh('/actueel/maaltijden/', 'Maaltijd bestaat niet!');
}

$maaltijd = new Maaltijd($maalid);

# Moet deze maaltijd gesloten worden?
if(($loginlid->hasPermission('P_MAAL_MOD') OR opConfide()) AND isset($_GET['sluit']) and $_GET['sluit'] == 1) {
	$maaltijd->sluit();
	SimpleHTML::invokeRefresh('/actueel/maaltijden/lijst/'.$maalid);
	exit;
}

require_once 'maaltijden/maaltijdlijstcontent.class.php';
$page = new MaaltijdLijstContent($maaltijd);

# Moeten we de fiscaal-lijst weergeven?
if(isset($_GET['fiscaal']) && $_GET['fiscaal']==1){
	$page->setFiscaal(true);
}
if($loginlid->hasPermission('P_MAAL_MOD') OR opConfide() OR $maaltijd->isTp() OR $maaltijd->isKok()){
	$page->view();
}else{
	SimpleHTML::invokeRefresh('/actueel/maaltijden/', 'U mag de maaltijdlijst niet bekijken.');
}


?>
