<?php

require_once 'configuratie.include.php';
require_once 'model/MededelingenModel.class.php';
require_once 'view/MededelingenView.class.php';

if (!MededelingenModel::isModerator()) {
	setMelding('U heeft daar niets te zoeken.', -1);
	redirect(CSR_ROOT . '/mededelingen');
}

if (isset($_GET['mededelingId']) AND is_numeric($_GET['mededelingId']) AND $_GET['mededelingId'] > 0) {
	try {
		$mededeling = new MededelingenModel((int) $_GET['mededelingId']);
	} catch (Exception $e) {
		setMelding('Mededeling met id ' . (int) $_GET['mededelingId'] . ' bestaat niet.', -1);
		redirect(CSR_ROOT . MededelingenView::mededelingenRoot);
	}
	$mededeling->keurGoed();
	setMelding('Mededeling is nu goedgekeurd.', 1);
	redirect(CSR_ROOT . MededelingenView::mededelingenRoot . $mededeling->getId());
} else {
	setMelding('Geen mededelingId gezet.', -1);
	redirect(CSR_ROOT . MededelingenView::mededelingenRoot);
}
