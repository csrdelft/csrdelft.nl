<?php
	require_once('inc.database.php');

//echo 'Titel voor controle: '.getGET('titel');

//	$t = $_GET['titel'];
//	print("
//		titel: $t<br>
//		urldecode: ".urldecode($t)."<br>
//		db_escape: ".db_escape($t)."<br>
//	");
	$titel 				= custom_escape('titel');
	$auteur				= custom_escape('auteur');
	$categorie		= (int) getGET('categorie');
	$taal 				= custom_escape('taal');
	$isbn 				= custom_escape('isbn');
	$uitgavejaar 	= (int) getGET('uitgavejaar');
	$paginas 			= (int) getGET('paginas');
	$beschrijving	= custom_escape('beschrijving');
	$code 				= custom_escape('code');
	$uitgeverij		= custom_escape('uitgeverij');
	
	$talenArray = getTalenArray();
	
	if (
		$titel == "" || 
		$auteur == "" ||
		$categorie == 0 ||
		$categorie == "" ||
		!in_array($taal, $talenArray) ||
		( $uitgavejaar != '' && !ereg('^[0-9]{4}$', $uitgavejaar) ) ||
		( $paginas != '' && !ereg('^[0-9]{1,4}$', $paginas) )
	) {
		// invoer niet okee
		$invoerOK = false;
	} else {
		// invoer wel okee
 		$invoerOK = true;
	}
	
	function custom_escape($unescapedStringGET) {
		// Hij komt mooi ge-escaped uit de Get, dus geen vuiltje aan de lucht!
		return getGET($unescapedStringGET);
	}
?>