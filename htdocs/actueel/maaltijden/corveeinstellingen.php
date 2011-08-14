<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/corveeinstellingen
# -------------------------------------------------------------------
# Hier worden instellingen van corveesysteem weergegeven en aangepast.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'maaltijden/corveeinstellingen.class.php';
require_once 'maaltijden/corveeinstellingencontent.class.php';


// Deze pagina is alleen voor de maalcie bedoeld.
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }


$corveeinstellingen = new Corveeinstellingen();

if($corveeinstellingen->isPostedFields() AND $corveeinstellingen->validFields() AND $corveeinstellingen->saveFields()){
	$melding = 'Wijzigingen zijn opgeslagen';
}else{
	$melding = $corveeinstellingen->getError();
}

$instellingen = new CorveeinstellingenContent($corveeinstellingen);
$instellingen->setMelding($melding);

$page=new csrdelft($instellingen);
$page->addStylesheet('maaltijd.css');
$page->view();

?>
