<?php

require_once 'configuratie.include.php';
require_once 'model/MededelingenModel.class.php';
require_once 'view/MededelingenView.class.php';

if (!MededelingenModel::isModerator()) {
	setMelding('U heeft daar niets te zoeken.', -1);
	redirect(CSR_ROOT . MededelingenView::mededelingenRoot);
}

$top3overzicht = new MededelingenOverzichtView();

$pagina = new CsrLayoutPage($top3overzicht);
$pagina->addCompressedResources('mededelingen');
$pagina->view();
