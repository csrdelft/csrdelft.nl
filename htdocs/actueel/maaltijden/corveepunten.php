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



require_once 'maaltijden/class.maaltrack.php';
require_once 'maaltijden/class.maaltijd.php';
$maaltrack = new MaalTrack();


require_once 'maaltijden/class.corveepuntencontent.php';
$punten = new CorveepuntenContent($maaltrack);


# actie is bewerken, kijken of velden ingevuld zijn
if(!$loginlid->hasPermission('P_MAAL_MOD'))

if(isset($_POST['actie'])){
	if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }

	$uid=$_POST['uid'];
	$actie=(int)$_POST['actie'];

	# bestaande maaltijd bewerken
	$corvee_kwalikok = (isset($_POST['corvee_kwalikok'])? 1 : 0);
	if($actie == 'bewerk' && (isset($_POST['corvee_punten'], $_POST['corvee_punten_bonus'], $_POST['corvee_vrijstelling'])))
		$maaltrack->editLid($uid, $corvee_kwalikok, $_POST['corvee_punten'], $_POST['corvee_punten_bonus'], $_POST['corvee_vrijstelling']);
}


$page=new csrdelft($punten);
$page->view();

?>
