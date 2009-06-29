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


require_once 'maaltijden/class.corveepuntencontent.php';
$punten = new CorveepuntenContent($maaltrack);


$page=new csrdelft($punten);
$page->view();

?>
