<?php

main();
exit;

function main() {
	// instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	
	// Het middenstuk
	if ($lid->hasPermission('P_LEDEN_READ')) {
		// een extra check om het testen makkelijker te maken
		file_exists('class.documenten_.php')
			? require_once('class.documenten_.php')
			: require_once('class.documenten.php');	
		$documenten = new Documenten($lid, $db);
		
		file_exists('class.documentencontent_.php')
			? require_once('class.documentencontent_.php')
			: require_once('class.documentencontent.php');
			
		$midden=new DocumentenContent($documenten);
	} else { // geen rechten
		$midden = new Includer('', 'geentoegang.html');
	}

	// zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	
	// pagina weergeven
	$pagina=new csrdelft($midden, $lid, $db);
	$pagina->setZijkolom($zijkolom);
	
	$pagina->view();
}
?>
