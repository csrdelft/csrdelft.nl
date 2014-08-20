<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingencontent.class.php';

if (!Mededeling::isModerator()) {
	invokeRefresh(CSR_ROOT . '/actueel/mededelingen', 'U heeft daar niets te zoeken.', -1);
}

if (isset($_GET['mededelingId']) AND is_numeric($_GET['mededelingId']) AND $_GET['mededelingId'] > 0) {
	try {
		$mededeling = new Mededeling((int) $_GET['mededelingId']);
	} catch (Exception $e) {
		invokeRefresh(CSR_ROOT . MededelingenContent::mededelingenRoot, 'Mededeling met id ' . (int) $_GET['mededelingId'] . ' bestaat niet.', -1);
	}
	$mededeling->keurGoed();
	invokeRefresh(CSR_ROOT . MededelingenContent::mededelingenRoot . $mededeling->getId(), 'Mededeling is nu goedgekeurd.', 1);
} else {
	invokeRefresh(CSR_ROOT . MededelingenContent::mededelingenRoot, 'Geen mededelingId gezet.', -1);
}
