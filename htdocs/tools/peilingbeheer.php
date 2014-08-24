<?php

/* Peiling beheerpagina */

require_once 'configuratie.include.php';
require_once 'peilingcontent.class.php';
require_once 'peiling.class.php';

$error = '';
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'toevoegen':
			if (isset($_POST['titel'], $_POST['opties']) AND Peiling::magBewerken()) {
				$properties['titel'] = $_POST['titel'];
				$properties['verhaal'] = $_POST['verhaal'];
				$properties['opties'] = array();
				foreach ($_POST['opties'] as $optie) {
					if (trim($optie) != '') {
						$properties['opties'][] = trim($optie);
					}
				}
				$peiling = Peiling::maakPeiling($properties);
				SimpleHTML::setMelding('De nieuwe peiling heeft id ' . $peiling->getId() . '.', 1);
				redirect(CSR_ROOT . '/tools/peilingbeheer.php');
				exit;
			}
			break;
		case 'stem':
			if (isset($_POST['id'])) {
				try {
					$peiling = new Peiling((int) $_POST['id']);
				} catch (Exception $e) {
					$error = $e->getMessage();
				}

				if (isset($_POST['optie']) && is_numeric($_POST['optie'])) {
					$peiling->stem((int) $_POST['optie']);
				}
				redirect(HTTP_REFERER . '#peiling' . $peiling->getId());
			}
			break;
		case 'verwijder':
			if (isset($_GET['id']) AND Peiling::magBewerken()) {
				try {
					$peiling = new Peiling((int) $_GET['id']);
					$peiling->deletePeiling();
					redirect(HTTP_REFERER);
				} catch (Exception $e) {
					$error = $e->getMessage();
				}
			}
			break;
	}
}


require_once 'peilingbeheercontent.class.php';
$beheer = new PeilingBeheerContent(Peiling::getLijst());

if ($error != '') {
	SimpleHTML::setMelding($error, -1);
}

if (!LoginModel::mag('P_LOGGED_IN') OR ! Peiling::magBewerken()) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$beheer = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

$pagina = new CsrLayoutPage($beheer);
$pagina->addScript('/layout/js/peilingbeheer');
$pagina->view();
