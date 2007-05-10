<?php

main();
exit;

function main() {
	// instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	
	// Het middenstuk
	if ($lid->hasPermission('P_DOCS_MOD')) {
		// een extra check om het testen makkelijker te maken
		file_exists('class.toevoegen_.php')
			? require_once('class.toevoegen_.php')
			: require_once('class.toevoegen.php');
		$upload = new Toevoegen($db, $lid);
		
		file_exists('class.toevoegencontent_.php')
		? require_once('class.toevoegencontent_.php')
		: require_once('class.toevoegencontent.php');
		$midden = new ToevoegenContent($upload);
	} else {
		// geen rechten
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