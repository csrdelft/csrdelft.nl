<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingencontent.class.php';
require_once 'mededelingen/mededelingtopdrieoverzichtcontent.class.php';

if (!Mededeling::isModerator()) {
	SimpleHTML::setMelding('U heeft daar niets te zoeken.', -1);
	redirect(CSR_ROOT . MededelingenContent::mededelingenRoot);
}

$top3overzicht = new MededelingTopDrieOverzichtContent();

$pagina = new CsrLayoutPage($top3overzicht);
$pagina->addStylesheet('/layout/css/mededelingen');
$pagina->view();
