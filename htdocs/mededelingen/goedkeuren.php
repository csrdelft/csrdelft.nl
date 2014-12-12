<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingencontent.class.php';

if (!Mededeling::isModerator()) {
	setMelding('U heeft daar niets te zoeken.', -1);
	redirect(CSR_ROOT . '/mededelingen');
}

if (isset($_GET['mededelingId']) AND is_numeric($_GET['mededelingId']) AND $_GET['mededelingId'] > 0) {
	try {
		$mededeling = new Mededeling((int) $_GET['mededelingId']);
	} catch (Exception $e) {
		setMelding('Mededeling met id ' . (int) $_GET['mededelingId'] . ' bestaat niet.', -1);
		redirect(CSR_ROOT . MededelingenContent::mededelingenRoot);
	}
	$mededeling->keurGoed();
	setMelding('Mededeling is nu goedgekeurd.', 1);
	redirect(CSR_ROOT . MededelingenContent::mededelingenRoot . $mededeling->getId());
} else {
	setMelding('Geen mededelingId gezet.', -1);
	redirect(CSR_ROOT . MededelingenContent::mededelingenRoot);
}
