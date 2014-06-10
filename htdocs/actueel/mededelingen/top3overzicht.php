<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingtopdrieoverzichtcontent.class.php';

if (!Mededeling::isModerator()) {
	header('location: ' . CSR_ROOT . MededelingenContent::mededelingenRoot);
	$_SESSION['mededelingen_foutmelding'] = 'U heeft daar niets te zoeken.';
	exit;
}

$top3overzicht = new MededelingTopDrieOverzichtContent();

$page = new CsrLayoutPage($top3overzicht);
$page->addStylesheet('mededelingen.css');
$page->view();
