<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingencontent.class.php';

if (!Mededeling::isModerator()) {
	SimpleHTML::setMelding('U heeft daar niets te zoeken.', -1);
	redirect(CSR_ROOT . '/actueel/mededelingen');
}

if (isset($_GET['mededelingId']) AND is_numeric($_GET['mededelingId']) AND $_GET['mededelingId'] > 0) {
	try {
		$mededeling = new Mededeling((int) $_GET['mededelingId']);
	} catch (Exception $e) {
		SimpleHTML::setMelding('Mededeling met id ' . (int) $_GET['mededelingId'] . ' bestaat niet.', -1);
		redirect(CSR_ROOT . MededelingenContent::mededelingenRoot);
	}
	$mededeling->keurGoed();
	SimpleHTML::setMelding('Mededeling is nu goedgekeurd.', 1);
	redirect(CSR_ROOT . MededelingenContent::mededelingenRoot . $mededeling->getId());
} else {
	SimpleHTML::setMelding('Geen mededelingId gezet.', -1);
	redirect(CSR_ROOT . MededelingenContent::mededelingenRoot);
}
