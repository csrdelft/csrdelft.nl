<?php
require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingtopdrieoverzichtcontent.class.php';

define('MEDEDELINGEN_ROOT','actueel/mededelingen/');

if(!Mededeling::isModerator()){
	header('location: '.CSR_ROOT.MEDEDELINGEN_ROOT);
	$_SESSION['mededelingen_foutmelding']='U heeft daar niets te zoeken.';
	exit;
}

$top3overzicht = new MededelingTopDrieOverzichtContent();

$page=new csrdelft($top3overzicht);
$page->addStylesheet('mededelingen.css');
$page->view();

?>