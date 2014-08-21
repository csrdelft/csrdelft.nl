<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingencontent.class.php';
require_once 'mededelingen/mededelingtopdrieoverzichtcontent.class.php';

if (!Mededeling::isModerator()) {
	invokeRefresh(CSR_ROOT . MededelingenContent::mededelingenRoot, 'U heeft daar niets te zoeken.', -1);
}

$top3overzicht = new MededelingTopDrieOverzichtContent();

$page = new CsrLayoutPage($top3overzicht);
$page->addStylesheet('/layout/css/mededelingen.css');
$page->view();
